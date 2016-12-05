//
//  FreehandDrawingLayer.h
//  DrawLayerTest
//

#import <QuartzCore/QuartzCore.h>

@interface FreehandDrawingLayer : CALayer

- (void)beginPathAtPoint:(CGPoint)point;
- (void)addNextPoint:(CGPoint)point;
- (void)endPath;

// Saving annotations
- (UIImage *)bakeSavedAnnotationWithXPerc:(CGFloat *)outXPerc
                                    yPerc:(CGFloat *)outYPerc
                                widthPerc:(CGFloat *)outWidthPerc
                                heightPerc:(CGFloat *)outHeightPerc;
- (void)clearCanvas;

+ (void)setStrokeColour:(UIColor *)strokeColour;
+ (void)setLineWidth:(CGFloat)lineWidth;
@end
