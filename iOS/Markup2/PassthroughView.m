//
//  PassthroughView.m
//  Markup2
//

#import "PassthroughView.h"

@implementation PassthroughView

- (BOOL)pointInside:(CGPoint)point withEvent:(UIEvent *)event
{
    for (UIView * view in [self subviews])
    {
        if (!view.hidden && [view pointInside:[self convertPoint:point
                                                          toView:view] withEvent:event])
        {
            return YES;
        }
    }
    return NO;
}

@end
