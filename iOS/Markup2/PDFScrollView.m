//    File: PDFScrollView.m
//Abstract: UIScrollView subclass that handles the user input to zoom the PDF page.  This class handles swapping the TiledPDFViews when the zoom level changes.
// Version: 1.0
//
//Disclaimer: IMPORTANT:  This Apple software is supplied to you by Apple
//Inc. ("Apple") in consideration of your agreement to the following
//terms, and your use, installation, modification or redistribution of
//this Apple software constitutes acceptance of these terms.  If you do
//not agree with these terms, please do not use, install, modify or
//redistribute this Apple software.
//
//In consideration of your agreement to abide by the following terms, and
//subject to these terms, Apple grants you a personal, non-exclusive
//license, under Apple's copyrights in this original Apple software (the
//"Apple Software"), to use, reproduce, modify and redistribute the Apple
//Software, with or without modifications, in source and/or binary forms;
//provided that if you redistribute the Apple Software in its entirety and
//without modifications, you must retain this notice and the following
//text and disclaimers in all such redistributions of the Apple Software.
//Neither the name, trademarks, service marks or logos of Apple Inc. may
//be used to endorse or promote products derived from the Apple Software
//without specific prior written permission from Apple.  Except as
//expressly stated in this notice, no other rights or licenses, express or
//implied, are granted by Apple herein, including but not limited to any
//patent rights that may be infringed by your derivative works or by other
//works in which the Apple Software may be incorporated.
//
//The Apple Software is provided by Apple on an "AS IS" basis.  APPLE
//MAKES NO WARRANTIES, EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION
//THE IMPLIED WARRANTIES OF NON-INFRINGEMENT, MERCHANTABILITY AND FITNESS
//FOR A PARTICULAR PURPOSE, REGARDING THE APPLE SOFTWARE OR ITS USE AND
//OPERATION ALONE OR IN COMBINATION WITH YOUR PRODUCTS.
//
//IN NO EVENT SHALL APPLE BE LIABLE FOR ANY SPECIAL, INDIRECT, INCIDENTAL
//OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
//SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
//INTERRUPTION) ARISING IN ANY WAY OUT OF THE USE, REPRODUCTION,
//MODIFICATION AND/OR DISTRIBUTION OF THE APPLE SOFTWARE, HOWEVER CAUSED
//AND WHETHER UNDER THEORY OF CONTRACT, TORT (INCLUDING NEGLIGENCE),
//STRICT LIABILITY OR OTHERWISE, EVEN IF APPLE HAS BEEN ADVISED OF THE
//POSSIBILITY OF SUCH DAMAGE.
//
//Copyright (C) 2010 Apple Inc. All Rights Reserved.
//

#import "PDFScrollView.h"
#import "TiledPDFView.h"
#import "AnnotationCanvasView.h"
#import <QuartzCore/QuartzCore.h>

#include <string.h>

@interface PDFScrollView ()

@property (nonatomic, strong) NSMutableArray *pageRects;
@property (nonatomic, strong) NSMutableArray *annotatedPages;
@end

@implementation PDFScrollView {
    CGPoint _pointToCenterAfterResize;
    CGFloat _scaleToRestoreAfterResize;
}

@synthesize pages;

- (id)initWithFrame:(CGRect)frame andPdfData:(NSData *)somePdfData
{
    if ((self = [super initWithFrame:frame])) {
		container = [[UIView alloc] initWithFrame:CGRectZero];
        [self addSubview:container];
		// Set up the UIScrollView
        self.showsVerticalScrollIndicator = YES;
        self.showsHorizontalScrollIndicator = YES;
        self.bouncesZoom = YES;
        self.decelerationRate = UIScrollViewDecelerationRateFast;
        self.delegate = self;
		[self setBackgroundColor:[UIColor grayColor]];
        minScale = 0.95;
        maxScale = 2.0;
		self.maximumZoomScale = maxScale;
		self.minimumZoomScale = minScale;
        [self setAutoresizingMask:UIViewAutoresizingFlexibleWidth|UIViewAutoresizingFlexibleHeight];
        [self setAutoresizesSubviews:YES];
        self.contentMode = UIViewContentModeScaleAspectFill;
		
		// Open the PDF document
        CGDataProviderRef pdfProvider = CGDataProviderCreateWithCFData((__bridge CFDataRef)somePdfData);
        pdf = CGPDFDocumentCreateWithProvider(pdfProvider);
        
        [self flashScrollIndicators];
        numPages  = CGPDFDocumentGetNumberOfPages(pdf);
        
        pages = [[NSMutableArray alloc] init];
        self.pageRects = [[NSMutableArray alloc] init];
        CGRect myRect = CGRectMake(0, 0, 0, 0);
        CGSize mySize = CGSizeMake(0, 0);
        pageSpace = 30.0;
        float contentHeight = 0;
        for (int i = 1; i <= numPages; i++) {
            pageRef = CGPDFDocumentGetPage(pdf, i);
            CGRect pageRect = CGPDFPageGetBoxRect(pageRef, kCGPDFMediaBox);
            pdfScale = self.frame.size.width/pageRect.size.width * 0.95;
            pageRect.size = CGSizeMake(pageRect.size.width*pdfScale, pageRect.size.height*pdfScale);
            pageRect.origin.y = contentHeight;
            TiledPDFView *pdfPageView = [[TiledPDFView alloc] initWithFrame:pageRect andScale:pdfScale];
            [pdfPageView setPage:pageRef];
            [pages addObject:pdfPageView];
            CGRect drawingFrame = CGRectMake(0.0, 0.0, pageRect.size.width, pageRect.size.height);
            AnnotationCanvasView *drawingView = [[AnnotationCanvasView alloc] initWithFrame:drawingFrame];
            drawingView.container = self;
            drawingView.pageNumber = i;
            [pdfPageView addSubview:drawingView];
            pdfPageView.annotationCanvas = drawingView;
            contentHeight += pageRect.size.height + pageSpace;
            [self.pageRects addObject:[NSValue valueWithCGRect:pageRect]];
            if(pageRect.size.width > mySize.width) {
                mySize.width = pageRect.size.width;
            }
            
            [pdfPageView setBackgroundImage];
        }
        mySize.height = contentHeight-pageSpace;
        myRect.size = mySize;
        [container setBounds:myRect];
        [self setContentSize:[container bounds].size];
        
        self.zoomScale = 1;
        
        for (TiledPDFView *aPdfView in pages) {
            [container addSubview:aPdfView.cachedPage];
            [container addSubview:aPdfView];
        }
        
        [[NSNotificationCenter defaultCenter] addObserver:self selector:@selector(setCurrentResponder:) name:@"currentResponder" object:nil];
        CGDataProviderRelease(pdfProvider);
        self.numAnnotations = 0;
    }
    return self;
}

- (void)pageNumberFadeOut {
    [UIView beginAnimations:nil context:NULL];
    [UIView setAnimationDuration:1];
    [self.pageNumberView setAlpha:0];
    [UIView commitAnimations];
}

-(void)lockZoom {
    maxScale = self.maximumZoomScale;
    minScale = self.minimumZoomScale;
    self.maximumZoomScale = 1.0;
    self.minimumZoomScale = 1.0;
}

-(void)unlockZoom {
    self.maximumZoomScale = maxScale;
    self.minimumZoomScale = minScale;
}

- (void)setCurrentResponder:(NSNotification *)not
{
    currentResponder = (UITextView *)[not object];
}

- (void)dealloc
{
    for(TiledPDFView *page in self.pages) {
        page.dontRender = YES;
    }
    NSLog(@"DEALLOCING PDF");
	// Clean up
    [[NSNotificationCenter defaultCenter] removeObserver:self name:@"currentResponder" object:nil];
    currentResponder = nil;
    CGPDFDocumentRelease(pdf);
}

#pragma mark -
#pragma mark Override layoutSubviews to center content

// We use layoutSubviews to center the PDF page in the view
- (void)layoutSubviews 
{
    [super layoutSubviews];
    
    // center the image as it becomes smaller than the size of the screen
    
    CGSize boundsSize = self.bounds.size;
    CGRect newContainerFrame = container.frame;
    if (newContainerFrame.size.width < boundsSize.width) {
        newContainerFrame.origin.x = (boundsSize.width - newContainerFrame.size.width) / 2;
    } else {
        newContainerFrame.origin.x = 0;
    }
    newContainerFrame.origin.y = 0;
    container.frame = newContainerFrame;
}

// Calculates what page we're currently scrolled to in the PDF. Should handle PDFs with
// pages 
- (int)getCurrentPageFromViewPosition:(CGPoint)position
{
    int pageNum = 0;
    float offsetSubtotal = 1.0;
    while (offsetSubtotal < position.y) {
        if( pageNum + 1 > [self.pageRects count]) {
            break;
        }
        offsetSubtotal += ([[self.pageRects objectAtIndex:pageNum] CGRectValue].size.height+pageSpace) * self.zoomScale;
        pageNum++;
    }
    if(pageNum == 0) {
        pageNum = 1;
    }
    return pageNum;
}

- (CGFloat)getStartYOfPage:(int)pageNumber {
    int startY = 0;
    if(pageNumber < 1) {
        pageNumber = 1;
    }
    for(int i=0; i<pageNumber-1; i++) {
        startY += ([[self.pageRects objectAtIndex:i] CGRectValue].size.height+pageSpace) * self.zoomScale;
    }
    return startY;
}

- (CGFloat)getHeightOfPage:(int)pageNumber {
    if(pageNumber < 1) {
        pageNumber = 1;
    }
    return [[self.pageRects objectAtIndex:pageNumber-1] CGRectValue].size.height;
}

- (void)scrollPDFBasedOnOffset:(int)yOffset {
    float newOffetY = self.contentOffset.y + yOffset;
    CGPoint newOffset = CGPointMake(0, newOffetY);
    [self setContentOffset:newOffset animated:YES];
}

#pragma mark - Annotation methods

- (void)addAnnotation:(Annotation *)annot
{
    int pageNum = annot.pageNumberValue;
    TiledPDFView *pageView = [self.pages objectAtIndex:(pageNum - 1)];
    AnnotationCanvasView *canvas = [pageView annotationCanvas];
    [canvas clearCanvas];
    [canvas addAnnotationViewForAnnotation:annot];
    self.numAnnotations++;
}

- (void)removeAllAnnotationViews {
    for(TiledPDFView *page in self.pages) {
        [[page annotationCanvas] removeAllAnnotations];
    }
}

- (void)setAnnotatingEnabled:(BOOL)annotatingEnabled
{
    //DLog(@"Enabling annotations? %@", annotatingEnabled ? @"Yes" : @"No");
    _annotatingEnabled = annotatingEnabled;
    for (TiledPDFView *pageView in self.pages) {
        AnnotationCanvasView *canvasView = [pageView annotationCanvas];
        [canvasView enableAnnotationInteraction:!annotatingEnabled];
    }
}

#pragma mark -
#pragma mark UIScrollView delegate methods

// A UIScrollView delegate callback, called when the user starts zooming. 
// We return our current TiledPDFView.

- (void)scrollViewWillBeginDragging:(UIScrollView *)scrollView {
    self.pageNumberView.alpha = 0.8;
}

- (void)scrollViewDidScroll:(UIScrollView *)scrollView
{
    CGPoint offset = [scrollView contentOffset];
    offset.y = offset.y + (self.frame.size.height/2);
    int page = [self getCurrentPageFromViewPosition:offset];
    if (page != currentPage) {
        [self.pdfScrollViewDelegate pdfScrollView:self didChangeToPage:[self getCurrentPageFromViewPosition:offset]];
        currentPage = page;
        [[self.pageNumberView pageNumberText] setText:[NSString stringWithFormat:@"Page %d",currentPage]];
    }
}

- (void)scrollViewDidEndDragging:(UIScrollView *)scrollView willDecelerate:(BOOL)decelerate {
    self.pageNumberView.alpha = 0.8;
    [self pageNumberFadeOut];
}

- (void)scrollViewDidEndScrollingAnimation:(UIScrollView *)scrollView {
}

- (UIView *)viewForZoomingInScrollView:(UIScrollView *)scrollView
{
    return container;
}

- (void)resetZoomToFit {
    int smallestWidth = self.frame.size.height-40.0;
    float newMinScale = smallestWidth/(container.frame.size.width/self.zoomScale);
    BOOL alreadyAtMin = NO;
    if(self.minimumZoomScale == self.zoomScale) {
        alreadyAtMin = YES;
    }
    
    self.minimumZoomScale = newMinScale;
    if(self.minimumZoomScale > self.zoomScale) {
        [self setZoomScale:newMinScale animated:YES];
    } else if (alreadyAtMin) {
        [self setZoomScale:newMinScale animated:YES];
    }
}

- (void)hideKeyboard:(id)sel
{
    [currentResponder resignFirstResponder];
}

- (void)scrollToPage:(int)pageNum withYOffset:(CGFloat)yOffset
{
    CGFloat pageOffset = [self getStartYOfPage:pageNum];
    CGFloat yOffsetAbsolute = yOffset * [[self.pageRects objectAtIndex:pageNum - 1] CGRectValue].size.height;
    CGFloat scrollOffset = pageOffset + yOffsetAbsolute;
    [self setContentOffset:CGPointMake(0.0, scrollOffset) animated:YES];
}

@end

