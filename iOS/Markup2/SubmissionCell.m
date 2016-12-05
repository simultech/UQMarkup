//
//  SubmissionCell.m
//  SectionCollectionViewTest
//

#import "SubmissionCell.h"
#import <QuartzCore/QuartzCore.h>

@interface SubmissionCell ()

@end

@implementation SubmissionCell {
    CGPDFPageRef _titlePage;
}

- (void)awakeFromNib
{
    [self.documentThumb setImage:[UIImage imageNamed:@"defaultsubmission.png"]];
    [self.documentThumb.layer setShadowOffset:CGSizeMake(0.0, 1.0)];
    [self.documentThumb.layer setShadowColor:[UIColor blackColor].CGColor];
    [self.documentThumb.layer setShadowRadius:8.0];
    [self.documentThumb.layer setShadowOpacity:0.7];
    [self.documentThumb.layer setShadowPath:[[UIBezierPath bezierPathWithRect:[self.documentThumb bounds]] CGPath]];
    
    //CGRect borderRect = CGRectInset(self.documentThumb.frame, -81.0, -81.0);
    //borderRect.size.height = borderRect.size.height/2;
    //borderRect.origin.x = 10;
    CGRect borderRect = CGRectInset(self.documentThumb.frame, -15.0, -15.0);
    borderRect.size.height = borderRect.size.height + 45;
    borderRect.size.width = borderRect.size.width + 4;
    
    self.selectedBackgroundView = [[UIView alloc] initWithFrame:borderRect];
    
    self.highlightView = [[UIView alloc] initWithFrame:borderRect];
    [self.highlightView setBackgroundColor:[UIColor redColor]];
    [self.selectedBackgroundView addSubview:self.highlightView];
    
    self.highlightView.layer.borderColor = [[UIColor darkGrayColor] CGColor];
    self.highlightView.layer.borderWidth = 3.0;
    self.highlightView.layer.cornerRadius = 14.0;
    [self.highlightView setBackgroundColor:[UIColor colorWithRGBHex:0xBBBDC0]];
    [self.highlightView setAlpha:0.8];
}

- (void)setTitlePage:(CGPDFPageRef)page {
    CGPDFPageRetain(page);
    _titlePage = page;
    
    CGRect pageRect = CGPDFPageGetBoxRect(_titlePage, kCGPDFMediaBox);
    float _PDFScale = self.frame.size.width / pageRect.size.width;
    pageRect.size = CGSizeMake(pageRect.size.width * _PDFScale, pageRect.size.height * _PDFScale);
    
    UIGraphicsBeginImageContext(pageRect.size);
    
    CGContextRef context = UIGraphicsGetCurrentContext();
    CGContextSetRGBFillColor(context, 1.0, 1.0, 1.0, 1.0);
    CGContextFillRect(context, pageRect);
    
    CGContextSaveGState(context);
    CGContextTranslateCTM(context, 0.0, pageRect.size.height);
    CGContextScaleCTM(context, 1.0, -1.0);
    CGContextScaleCTM(context, _PDFScale, _PDFScale);
    CGContextDrawPDFPage(context, _titlePage);
    
    CGContextRestoreGState(context);
    
    UIImage *titlePageImage = UIGraphicsGetImageFromCurrentImageContext();
    UIGraphicsEndImageContext();
    
    CGPDFPageRelease(_titlePage);
    
    self.documentThumb.image = titlePageImage;
}

- (void)dealloc
{
    CGPDFPageRelease(_titlePage);
}

@end
