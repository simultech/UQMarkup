//    File: PDFScrollView.h
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

#import <UIKit/UIKit.h>
#import "Annotation.h"
#import "PageNumberView.h"

@protocol PDFScrollViewDelegate;

@class TiledPDFView;

@interface PDFScrollView : UIScrollView <UIScrollViewDelegate> {
	// The TiledPDFView that is currently front most
	TiledPDFView *pdfView;

	// current pdf zoom scale
	CGFloat pdfScale;
	
	CGPDFDocumentRef pdf;
    CGPDFPageRef pageRef;
    
    int currentPage;
    
    int numPages;
    float pageSpace;
    
    UIView *container;
    float minScale;
    float maxScale;
    
    
    UITextView *currentResponder;
}

@property (nonatomic, strong) NSMutableArray *pages;
@property (nonatomic, weak) id<PDFScrollViewDelegate> pdfScrollViewDelegate;
@property (nonatomic, assign) BOOL annotatingEnabled;
@property (nonatomic, assign) int numAnnotations;

@property (nonatomic, assign) BOOL readyToRecord;
@property (nonatomic, assign) BOOL readyToEnterText;
@property (nonatomic, assign) BOOL readyToErase;
@property (nonatomic, strong) PageNumberView *pageNumberView;

- (id)initWithFrame:(CGRect)frame andPdfData:(NSData *)pdf;
- (int)getCurrentPageFromViewPosition:(CGPoint)position;
- (void)setCurrentResponder:(NSNotification *)not;
- (void)hideKeyboard:(id)sel;
- (CGFloat)getStartYOfPage:(int)pageNumber;
- (CGFloat)getHeightOfPage:(int)pageNumber;
- (void)lockZoom;
- (void)unlockZoom;
- (void)addAnnotation:(Annotation *)annot;
- (void)resetZoomToFit;
- (void)removeAllAnnotationViews;
- (void)pageNumberFadeOut;
- (void)scrollPDFBasedOnOffset:(int)yOffset;
- (void)scrollToPage:(int)pageNum withYOffset:(CGFloat)yOffset;
@end

@class Annotation;
@protocol PDFScrollViewDelegate
- (void)pdfScrollView:(PDFScrollView *)scrollView didChangeToPage:(int)pageNum;

- (void)pdfScrollView:(PDFScrollView *)scrollView
 didAddAnnotationType:(AnnotationType)annotType
            withTitle:(NSString *)text
               colour:(UIColor *)colour
               onPage:(int)pageNum
               atXPos:(float)xPercentage
                 yPos:(float)yPercentage
                width:(float)widthPercentage
               height:(float)heightPercentage;

@end
