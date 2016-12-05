//
//  FreehandDrawingView.m
//  DrawLayerTest
//

#import "AnnotationCanvasView.h"
#import <QuartzCore/QuartzCore.h>
#import "FreehandDrawingLayer.h"
#import "PDFScrollView.h"
#import "AudioAnnotationView.h"
#import "TextAnnotationView.h"
#import "PalmProtectViewController.h"
#import "AnnotationSettingsManager.h"

@interface AnnotationCanvasView () <EditableAnnotationViewDelegate, TextAnnotationViewDelegate, UIGestureRecognizerDelegate>

@property (nonatomic, strong) FreehandDrawingLayer *drawingLayer;
@property (nonatomic, strong) NSMutableArray *annotationViews;

@property (nonatomic, strong) UITapGestureRecognizer *tap;
@property (nonatomic, strong) UITapGestureRecognizer *deselectRecogniser;

@property (assign) int returnYTextOffset;
@property (nonatomic, strong) TextAnnotationView *temporaryTextView;

@end

@implementation AnnotationCanvasView

- (id)initWithFrame:(CGRect)frame
{
    self = [super initWithFrame:frame];
    if (self) {
        
        // Initialization code
        self.autoresizingMask = UIViewAutoresizingFlexibleHeight|UIViewAutoresizingFlexibleWidth;
        self.drawingLayer = [[FreehandDrawingLayer alloc] init];
        self.drawingLayer.frame = self.frame;
        [self.layer addSublayer:self.drawingLayer];
        self.tap = [[UITapGestureRecognizer alloc] initWithTarget:self action:@selector(addAnnotationHere:)];
        self.tap.delegate = self;
        [self addGestureRecognizer:self.tap];
        self.multipleTouchEnabled = YES;
        self.annotationViews = [[NSMutableArray alloc] init];
        
        self.deselectRecogniser = [[UITapGestureRecognizer alloc] initWithTarget:self action:@selector(deselectAnns:)];
        self.deselectRecogniser.delegate = self;
        [self.deselectRecogniser setEnabled:NO];
        [self addGestureRecognizer:self.deselectRecogniser];
    }
    return self;
}

- (void)layoutSubviews
{
    self.drawingLayer.frame = self.frame;
}

- (BOOL)lineNearPoint:(CGPoint)point
{
    BOOL foundLine = NO;

    CGRect checkRect = CGRectMake(point.x - 11.0, point.y - 11.0, 22.0, 22.0);
    
    size_t width = checkRect.size.width;
    size_t height = checkRect.size.height;
    uint8_t *bits = calloc(width * height, sizeof(uint8_t));
    
    CGContextRef ctx = CGBitmapContextCreate(bits, width, height, sizeof(uint8_t) * 8, width, NULL, kCGImageAlphaOnly);
    CGContextSetShouldAntialias(ctx, NO);
    CGContextTranslateCTM(ctx, 0.0, checkRect.size.height);
    CGContextScaleCTM(ctx, 1.0, -1.0);
    CGContextTranslateCTM(ctx, -checkRect.origin.x, -checkRect.origin.y);
    [self.layer renderInContext:ctx];
    
    for (NSUInteger x = 0 ; x < width; ++x) {
        for (NSUInteger y = 0; y < height; ++y) {
            if (bits[y * width + x] > 128) {
                foundLine = YES;
            }
        }
    }
    
    CGContextRelease(ctx);
    free(bits);
    
    return foundLine;
    
}

- (void)addAnnotationViewForAnnotation:(Annotation *)ann
{
    CGFloat xPos = ann.xPosValue * self.frame.size.width;
    CGFloat yPos = ann.yPosValue * self.frame.size.height;
    EditableAnnotationView *editAnnView = [[EditableAnnotationView alloc] initWithFrame:CGRectZero];
    [editAnnView setDelegate:self];
    [editAnnView setAnnotation:ann];
    
    if ([ann.annotationType isEqualToString:@"Recording"]) {
        CGRect badgeFrame = CGRectMake(xPos - 15.0, yPos - 15.0, 30.0, 30.0);
        AudioAnnotationView *audioView = [[AudioAnnotationView alloc] initWithFrame:badgeFrame andAudioAnnotation:ann];
        
        [editAnnView setAnnotationContentView:audioView];
        
    } else if ([ann.annotationType isEqualToString:@"Text"]) {
        CGRect annRect = CGRectMake(xPos, yPos, ann.widthValue * self.bounds.size.width, ann.heightValue * self.bounds.size.height);
        TextAnnotationView *tv = [[TextAnnotationView alloc] initWithFrame:annRect];
        tv.textView.text = ann.title;
        tv.textView.editable = NO;
        tv.textView.backgroundColor = [UIColor clearColor];
        tv.textView.userInteractionEnabled = NO;
        tv.textView.textColor = [UIColor colorWithHexString:ann.colour];
        tv.textView.font = [UIFont systemFontOfSize:14.0];
        [editAnnView setAnnotationContentView:tv];
    } else {
        CGRect annRect = CGRectMake(xPos,yPos, ann.widthValue * self.bounds.size.width, ann.heightValue * self.bounds.size.height);
        UIImageView *annView = [[UIImageView alloc] initWithFrame:annRect];
        NSLog(@"Ann's image path: %@", [ann localFilePath]);
        annView.image = [UIImage imageWithContentsOfFile:[ann localFilePath]];
        [annView setAutoresizesSubviews:YES];
        [annView setContentMode:UIViewContentModeScaleAspectFit];
        
        
        [editAnnView setAnnotationContentView:annView];
    }
    [self.annotationViews addObject:editAnnView];
    [self addSubview:editAnnView];
}

- (void)removeAllAnnotations {
    for(UIView *annotation in self.annotationViews) {
        [annotation removeFromSuperview];
    }
    [self.annotationViews removeAllObjects];
}

- (void)deselectAnns:(UITapGestureRecognizer *)tap
{
    [self.selectedAnnView setAnnotationSelected:NO];
    self.selectedAnnView = nil;
}

- (id)getPDFDelegate {
    return self.container;
}

- (void)doEraserAtCGPoint:(CGPoint)point withRadius:(int)radius {
    CGPoint globalPoint = [[[UIApplication sharedApplication] keyWindow] convertPoint:point fromView:self];
    NSLog(@"Erasing at %f %f size %d",globalPoint.x,globalPoint.y,radius);
    for(int i=0; i<[self.subviews count]; i++) {
        if([@"EditableAnnotationView" isEqualToString:[NSString stringWithFormat:@"%@",[[self.subviews objectAtIndex:i] class]]]) {
            EditableAnnotationView *annView = [self.subviews objectAtIndex:i];
            if([annView.annotation.annotationType isEqualToString:@"Freehand"] || [annView.annotation.annotationType isEqualToString:@"Highlight"]) {
                CGPoint annFramePoint = annView.frame.origin;
                CGPoint annPointTL = [[[UIApplication sharedApplication] keyWindow] convertPoint:annFramePoint fromView:self];
                annFramePoint.x = annFramePoint.x + annView.frame.size.width;
                annFramePoint.y = annFramePoint.y + annView.frame.size.height;
                CGPoint annPointBR = [[[UIApplication sharedApplication] keyWindow] convertPoint:annFramePoint fromView:annView];
                if(annPointTL.x < globalPoint.x && annPointTL.y < globalPoint.y && annPointBR.x > globalPoint.x && annPointBR.y > globalPoint.y) {
                    CGPoint relativePoint = point;
                    relativePoint.x = relativePoint.x - annView.frame.origin.x;
                    relativePoint.y = relativePoint.y - annView.frame.origin.y;
                    [annView eraseNearPoint:relativePoint withRadius:radius];
                    //annView.alpha = annView.alpha - 0.05;
                }
            }
        }
    }
}

#pragma mark Touch handlers
- (void)touchesBegan:(NSSet *)touches withEvent:(UIEvent *)event
{
    if (!self.container.annotatingEnabled) {
        return;
    }

    if(![[PalmProtectViewController palmProtectType] isEqualToString:@"Disabled"] && self.activeTouch) {
        //We have to check for palm
        CGPoint newTouchPoint = [[touches anyObject] locationInView:self];
        CGPoint currentTouchPoint = [self.activeTouch locationInView:self];
        DLog(@"TEMP TOUCH %g %g",newTouchPoint.x,newTouchPoint.y);
        DLog(@"ACTIVE TOUCH %g %g",currentTouchPoint.x,currentTouchPoint.y);
        BOOL deleteCurrentTouch = NO;
        if([[PalmProtectViewController palmProtectType] isEqualToString:@"Left"]) {
            DLog(@"CHECKING LEFT");
            if((self.frame.size.width - newTouchPoint.x)+newTouchPoint.y < (self.frame.size.width - currentTouchPoint.x)+currentTouchPoint.y) {
                deleteCurrentTouch = YES;
            }
        } else if([[PalmProtectViewController palmProtectType] isEqualToString:@"Right"]) {
            DLog(@"CHECKING RIGHT");
            if(newTouchPoint.x+newTouchPoint.y < currentTouchPoint.x+currentTouchPoint.y) {
                deleteCurrentTouch = YES;
            }
        }
        if(deleteCurrentTouch) {
            self.isTouching = NO;
            self.activeTouch = nil;
            [self.drawingLayer endPath];
            DLog(@"NO LONGER THE MAIN ONE");
            
        }
    }
    if(!self.isTouching) {
        UITouch *touch = [touches anyObject];
        CGPoint touchPoint = [touch locationInView:self];
        [self.drawingLayer beginPathAtPoint:touchPoint];
        DLog(@"STARTING TO DRAW PATH");
        self.activeTouch = touch;
    }
    self.isTouching = YES;
}

- (void)touchesMoved:(NSSet *)touches withEvent:(UIEvent *)event
{
    if (!self.container.annotatingEnabled) {
        return;
    }
    
    if(self.activeTouch) {
        CGPoint touchPoint = [self.activeTouch locationInView:self];
        [self.drawingLayer addNextPoint:touchPoint];
        if(self.container.readyToErase) {
            [self doEraserAtCGPoint:touchPoint withRadius:[[AnnotationSettingsManager sharedManager] eraserWidth]];
        }
    }
}

- (void)touchesEnded:(NSSet *)touches withEvent:(UIEvent *)event
{
    self.activeTouch = nil;
    if (!self.container.annotatingEnabled) {
        self.isTouching = NO;
        return;
    }
    if(self.isTouching) {
        [self.drawingLayer endPath];
        self.isTouching = NO;
    }
}

- (void)touchesCancelled:(NSSet *)touches withEvent:(UIEvent *)event
{
    self.activeTouch = nil;
    if (!self.container.annotatingEnabled) {
        return;
    }
    if(self.isTouching) {
        [self.drawingLayer endPath];
        self.isTouching = NO;
    }
}

+ (void)setLineWidth:(CGFloat)lineWidth
{
    [FreehandDrawingLayer setLineWidth:lineWidth];
}

+ (void)setStrokeColour:(UIColor *)strokeColour
{
    [FreehandDrawingLayer setStrokeColour:strokeColour];
}

- (void)addAnnotationHere:(UITapGestureRecognizer *)tap
{
    CGPoint tapPos = [tap locationInView:self];
    double xPos = tapPos.x/self.frame.size.width;
    double yPos = tapPos.y/self.frame.size.height;
    if (self.container.readyToErase) {
        self.container.readyToErase = NO;
    }
    if (self.container.readyToRecord) {
        NSString *title = [NSString stringWithFormat:@"Annotation %d", (self.container.numAnnotations + 1)];
        [self.container.pdfScrollViewDelegate pdfScrollView:self.container didAddAnnotationType:AnnotationTypeRecording withTitle:title colour:nil onPage:self.pageNumber atXPos:xPos yPos:yPos width:30.0 height:30.0];
        self.container.readyToRecord = NO;
    } else if (self.container.readyToEnterText) {
        self.container.readyToEnterText = NO;
        [self.container setScrollEnabled:YES];
        [self insertTextAtPoint:tapPos];
    }
    
}

- (UIImage *)bakeSavedAnnotationWithXPerc:(CGFloat *)outXPerc yPerc:(CGFloat *)outYPerc widthPerc:(CGFloat *)outWidthPerc heightPerc:(CGFloat *)outHeightPerc pageNum:(int *)pageNum
{
    *pageNum = self.pageNumber;
    return [self.drawingLayer bakeSavedAnnotationWithXPerc:outXPerc
                                                     yPerc:outYPerc
                                                 widthPerc:outWidthPerc
                                                heightPerc:outHeightPerc];
}

- (void)clearCanvas
{
    [self.drawingLayer clearCanvas];
}

- (void)enableAnnotationInteraction:(BOOL)enabled
{
    for (EditableAnnotationView *annView in self.annotationViews) {
        [annView setUserInteractionEnabled:enabled];
    }
}
                 
#pragma mark EditableAnnotationViewDelegate methods
- (void)annViewDidSelectAnnotation:(EditableAnnotationView *)annView
{
    [self.selectedAnnView setAnnotationSelected:NO];
    self.selectedAnnView = annView;
    self.container.scrollEnabled = NO;
    [self.deselectRecogniser setEnabled:YES];
}

- (void)annViewDidDeselectAnnotation:(EditableAnnotationView *)annView
{
//    if (annView == self.selectedAnnView) {
//        self.selectedAnnView = nil;
//    }
    self.container.scrollEnabled = YES;
    [self.deselectRecogniser setEnabled:NO];
}

- (void)annViewDidDeleteAnnotation:(EditableAnnotationView *)annView
{
    [self.annotationViews removeObject:annView];
}

- (void)annViewDidStartEditingContents:(EditableAnnotationView *)annView
{
    self.temporaryTextView = (TextAnnotationView *)[annView annotationContentView];
    self.temporaryTextView.delegate = self;
    CGPoint globalPoint = annView.frame.origin;
    
    float yOffset = 0;
    CGRect viewSize = self.frame;
    yOffset = viewSize.size.height-globalPoint.y;
    self.returnYTextOffset = 0;
    CGFloat epsilon = 450 + annView.frame.size.height + 50;
    if(yOffset < epsilon) {
        [self.container scrollPDFBasedOnOffset:epsilon-yOffset];
        self.returnYTextOffset = yOffset-epsilon;
    }
}

- (void)insertTextAtPoint:(CGPoint)point
{
    NSLog(@"INSERT TEXT AT POINT");
    self.container.readyToEnterText = NO;
    CGFloat width = self.frame.size.width - 20.0 - point.x;
    
    CGPoint globalPoint = [self.superview.superview.superview.superview convertPoint:point fromView:self];
    
    float yOffset = 0;
    CGRect viewSize = self.superview.superview.superview.superview.frame;
    yOffset = viewSize.size.height-globalPoint.y;
    self.returnYTextOffset = 0;
    if(yOffset < 450) {
        [self.container scrollPDFBasedOnOffset:450-yOffset];
        self.returnYTextOffset = yOffset-450;
    }

    self.temporaryTextView = [[TextAnnotationView alloc] initWithFrame:CGRectMake(point.x-10, point.y-16, width, 24.0)];
    self.temporaryTextView.delegate = self;
    [self addSubview:self.temporaryTextView];
    [self bringSubviewToFront:self.temporaryTextView];
    [self.temporaryTextView editContents];
}

- (void)forceEndTextEditing {
    if(self.temporaryTextView) {
        [self.temporaryTextView textViewDidEndEditing:self.temporaryTextView.textView];
    }
}

#pragma mark TextAnnotationViewDelegate methods
- (void)textAnnotationView:(TextAnnotationView *)textView willBeginEditingTextAtPoint:(CGPoint)point
{
    
}

- (void)textAnnotationView:(TextAnnotationView *)textView didFinishEditingTextAtPoint:(CGPoint)point withSize:(CGSize)size updatingExisting:(BOOL)updating
{
    /* Dont remove this Justin, its magical */
    if(self.temporaryTextView) {
        self.temporaryTextView = nil;
    
        double xPos = point.x/self.frame.size.width;
        double yPos = point.y/self.frame.size.height;
        double width = size.width / self.frame.size.width;
        double height = size.height / self.frame.size.height;
        if (!updating) {
            [self.container.pdfScrollViewDelegate pdfScrollView:self.container didAddAnnotationType:AnnotationTypeText withTitle:textView.textView.text colour:[[AnnotationSettingsManager sharedManager] textColor] onPage:self.pageNumber atXPos:xPos yPos:yPos width:width height:height];
            [textView removeFromSuperview];
        } else {
            EditableAnnotationView *editView = (EditableAnnotationView *)textView.superview;
            editView.frame = CGRectMake(editView.frame.origin.x, editView.frame.origin.y, size.width + (kBGMargin * 2), size.height + (kBGMargin * 2));
            Annotation *ann = editView.annotation;
            ann.height = @(height);
            ann.width = @(width);
            [[NSManagedObjectContext defaultContext] save];
        }
        [self.container scrollPDFBasedOnOffset:self.returnYTextOffset];
    }
}

@end
