//
//  FreehandDrawingView.h
//  DrawLayerTest
//

#import <UIKit/UIKit.h>
#import "Annotation.h"
#import "EditableAnnotationView.h"

@class PDFScrollView;
@interface AnnotationCanvasView : UIView
@property (nonatomic, weak) PDFScrollView *container;
@property (nonatomic, strong) EditableAnnotationView *selectedAnnView;
@property (nonatomic, assign) NSInteger pageNumber;
@property (nonatomic, strong) UITouch *activeTouch;
@property (nonatomic, assign) BOOL isTouching;

+ (void)setLineWidth:(CGFloat)lineWidth;
+ (void)setStrokeColour:(UIColor *)strokeColour;

- (void)addAnnotationViewForAnnotation:(Annotation *)ann;
- (void)removeAllAnnotations;

- (void)forceEndTextEditing;

- (UIImage *)bakeSavedAnnotationWithXPerc:(CGFloat *)outXPerc yPerc:(CGFloat *)outYPerc widthPerc:(CGFloat *)outWidthPerc heightPerc:(CGFloat *)outHeightPerc pageNum:(int *)pageNum;
- (void)clearCanvas;

- (void)enableAnnotationInteraction:(BOOL)enabled;
@end
