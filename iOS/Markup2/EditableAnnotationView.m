//
//  EditableAnnotationView.m
//  Markup2
//

#import "EditableAnnotationView.h"
#import <QuartzCore/QuartzCore.h>
#import "Annotation.h"
#import "LibraryAnnotation.h"
#import "TextAnnotationView.h"
#import "AudioAnnotationManager.h"

@interface EditableAnnotationView () <UIGestureRecognizerDelegate>

@property (nonatomic, strong) UITapGestureRecognizer *tap;
@property (nonatomic, strong) UIPanGestureRecognizer *pan;
@end

@implementation EditableAnnotationView {
    BOOL _isSelected;
}

- (id)initWithFrame:(CGRect)frame
{
    self = [super initWithFrame:frame];
    if (self) {
        // Initialization code
        UILongPressGestureRecognizer *longPress = [[UILongPressGestureRecognizer alloc] initWithTarget:self action:@selector(showEditMenu:)];
        [self addGestureRecognizer:longPress];
        
        self.tap = [[UITapGestureRecognizer alloc] initWithTarget:self action:@selector(setSelected:)];
        self.tap.delegate = self;
        self.tap.cancelsTouchesInView = NO;
        [self addGestureRecognizer:self.tap];
        
        self.pan = [[UIPanGestureRecognizer alloc] initWithTarget:self action:@selector(dragSelf:)];
        self.backgroundColor = [UIColor clearColor];
        _isSelected = NO;
        self.exclusiveTouch = NO;
    }
    return self;
}

- (BOOL)canBecomeFirstResponder
{
    return YES;
}

- (void)setAnnotationContentView:(UIView *)annotationContentView
{
    if (_annotationContentView) {
        [_annotationContentView removeFromSuperview];
    }
    CGRect contentViewBounds = annotationContentView.frame;
    self.frame = CGRectInset(contentViewBounds, -kBGMargin, -kBGMargin);
    annotationContentView.frame = CGRectMake(kBGMargin, kBGMargin, contentViewBounds.size.width, contentViewBounds.size.height);
    _annotationContentView = annotationContentView;
    [self addSubview:_annotationContentView];
}

#pragma mark - Annotation Edit Menu
- (void)showEditMenu:(UILongPressGestureRecognizer *)tapGestureRecogniser
{
    [self becomeFirstResponder];
    
    UIMenuController *menuController = [UIMenuController sharedMenuController];
    
    if (![menuController isMenuVisible] && ![AudioAnnotationManager isRecording]) {
        UIMenuItem *deleteItem = [[UIMenuItem alloc] initWithTitle:@"Delete" action:@selector(deleteSelf:)];
        UIMenuItem *saveItem = [[UIMenuItem alloc] initWithTitle:@"Save" action:@selector(saveToLibrary:)];
        
        CGRect menuRect = [self convertRect:self.bounds toView:self.superview];
        [menuController setTargetRect:menuRect inView:self.superview];
        if ([self.annotation.annotationType isEqualToString:@"Text"]) {
            UIMenuItem *editItem = [[UIMenuItem alloc] initWithTitle:@"Edit" action:@selector(editText:)];
            [menuController setMenuItems:@[saveItem, editItem, deleteItem]];
        } else {
            [menuController setMenuItems:@[saveItem, deleteItem]];
        }
        
        [menuController setMenuVisible:YES animated:YES];
        
        [menuController setMenuItems:nil];
    }
}

- (void)setSelected:(UITapGestureRecognizer *)tap
{
    NSLog(@"SEEING SELECTED");
   [self removeGestureRecognizer:self.pan];
    if(!_isSelected) {
        [self addGestureRecognizer:self.pan];
    }
    [self setAnnotationSelected:!_isSelected];

}

- (void)setAnnotationSelected:(BOOL)selected
{
    static UIColor *selectedColour, *deselectedColour;
    if (!selectedColour) {
        selectedColour = [UIColor colorWithRed:0.7 green:0.7 blue:0.7 alpha:0.3];
        deselectedColour = [UIColor clearColor];
    }
    
    UIMenuController *menuController = [UIMenuController sharedMenuController];
    if ([menuController isMenuVisible]) {
        [menuController setMenuVisible:NO animated:YES];
    }
    
    [UIView beginAnimations:nil context:nil];
    [UIView setAnimationCurve:UIViewAnimationCurveEaseInOut];
    [UIView setAnimationDuration:0.2];
    
    if (selected) {
        [self setBackgroundColor:selectedColour];
        _isSelected = YES;
        [self.tap setEnabled:NO];
        [self.delegate annViewDidSelectAnnotation:self];
    } else {
        [self removeGestureRecognizer:self.pan];
        [self setBackgroundColor:deselectedColour];
        [self.tap setEnabled:YES];
        [self.delegate annViewDidDeselectAnnotation:self];
        _isSelected = NO;
    }
    
    [UIView commitAnimations];
}

- (void)deleteSelf:(id)sender
{
    [[NSNotificationCenter defaultCenter] postNotificationName:@"Create_Log" object:@{@"type":@"Annotation",@"action":@"Delete",@"value":self.annotation.annotationType}];
    [self.annotation deleteLocalFile];
    [self.annotation deleteEntity];
    [[NSManagedObjectContext defaultContext] save];
    [self removeFromSuperview];
    [self.delegate annViewDidDeleteAnnotation:self];
}

- (void)saveToLibrary:(id)sender
{
    [[NSNotificationCenter defaultCenter] postNotificationName:@"Create_Log" object:@{@"type":@"Library",@"action":@"Save",@"value":self.annotation.annotationType}];
    LibraryAnnotation *libAnn = [LibraryAnnotation createEntity];
    [libAnn populateFromAnnotation:self.annotation];
    [[NSManagedObjectContext defaultContext] save];
}

- (void)editText:(id)sender
{
    TextAnnotationView *textView = (TextAnnotationView *)self.annotationContentView;
    [self.delegate annViewDidStartEditingContents:self];
    [textView editContents];
}

- (BOOL)lineNearPoint:(CGPoint)point
{
    BOOL foundLine = NO;
    
    CGRect checkRect = CGRectMake(point.x - 12, point.y - 12, 24.0, 24.0);
    
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
            if (bits[y * width + x] > 0) {
                foundLine = YES;
            }
        }
    }
    
    CGContextRelease(ctx);
    free(bits);
    
    return foundLine;
    
}

- (void)eraseNearPoint:(CGPoint)point withRadius:(int)radius
{
    BOOL foundLine = NO;
    
    CGRect checkRect = CGRectMake(point.x - radius, point.y - radius, radius*2, radius*2);
    
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
            if (bits[y * width + x] > 0) {
                foundLine = YES;
            }
        }
    }
    if(foundLine) {
        if([[NSString stringWithFormat:@"%@",[self.annotationContentView class]] isEqualToString:@"UIImageView"]) {
            double scale = [UIScreen mainScreen].scale;
            NSLog(@"ERASING STUFF %f %f at %f",point.x,point.y,scale);
            UIImage *img = [(UIImageView *)self.annotationContentView image];
            CGSize s = img.size;
            UIGraphicsBeginImageContext(s);
            CGContextRef context = UIGraphicsGetCurrentContext();
            CGContextBeginPath(context);
            CGContextAddArc(context, (point.x-20)*scale, (point.y-20)*scale, radius, 0.0, 2*M_PI, 0);
            CGContextAddRect(context,CGRectMake(0,0,s.width,s.height));
            CGContextEOClip(context);
            [img drawAtPoint:CGPointZero];
            [(UIImageView *)self.annotationContentView setImage:UIGraphicsGetImageFromCurrentImageContext()];
            UIGraphicsEndImageContext();
        }
        
    } else {
        NSLog(@"NOT ERASING STUFF");
    }
    
    CGContextRelease(ctx);
    free(bits);
}


#pragma mark - for dragging
- (void)dragSelf:(UIPanGestureRecognizer *)pan
{
    if ([[UIMenuController sharedMenuController] isMenuVisible]) {
        [[UIMenuController sharedMenuController] setMenuVisible:NO];
    }
    
    if (_isSelected) {
        UIView *piece = [pan view];
        
        [self adjustAnchorPointForGestureRecognizer:pan];
        
        if ([pan state] == UIGestureRecognizerStateBegan || [pan state] == UIGestureRecognizerStateChanged) {
            CGPoint translation = [pan translationInView:[piece superview]];
            [piece setCenter:CGPointMake([piece center].x + translation.x, [piece center].y + translation.y)];
            [pan setTranslation:CGPointZero inView:[piece superview]];
            
            //Lock to bounds
            if(self.frame.origin.x < 0) {
                CGRect newFrame = self.frame;
                newFrame.origin.x = 0;
                self.frame = newFrame;
            }
            if(self.frame.origin.y < 0) {
                CGRect newFrame = self.frame;
                newFrame.origin.y = 0;
                self.frame = newFrame;
            }
            if(self.frame.origin.x + self.frame.size.width > self.superview.frame.size.width) {
                CGRect newFrame = self.frame;
                newFrame.origin.x = self.superview.frame.size.width - self.frame.size.width;
                self.frame = newFrame;
            }
            if(self.frame.origin.y + self.frame.size.height > self.superview.frame.size.height) {
                CGRect newFrame = self.frame;
                newFrame.origin.y = self.superview.frame.size.height - self.frame.size.height;
                self.frame = newFrame;
            }
        }
    }
}

// scale and rotation transforms are applied relative to the layer's anchor point
// this method moves a gesture recognizer's view's anchor point between the user's fingers
- (void)adjustAnchorPointForGestureRecognizer:(UIGestureRecognizer *)gestureRecognizer
{
    UIView *piece = gestureRecognizer.view;
    CGPoint locationInView = [gestureRecognizer locationInView:piece];
    CGPoint locationInSuperview = [gestureRecognizer locationInView:piece.superview];
    
    if (gestureRecognizer.state == UIGestureRecognizerStateBegan) {
        self.layer.anchorPoint = CGPointMake(locationInView.x / piece.bounds.size.width, locationInView.y / piece.bounds.size.height);
        piece.center = locationInSuperview;
        
    } else if (gestureRecognizer.state == UIGestureRecognizerStateEnded || gestureRecognizer.state == UIGestureRecognizerStateCancelled) {
        
        self.annotation.xPosValue = (piece.frame.origin.x + kBGMargin) / self.superview.frame.size.width;
        self.annotation.yPosValue = (piece.frame.origin.y + kBGMargin) / self.superview.frame.size.height;
        [[NSManagedObjectContext defaultContext] save];
    }
}

- (BOOL)pointInside:(CGPoint)point withEvent:(UIEvent *)event
{
    if ([self lineNearPoint:point]) {
        return YES;
    }
    return NO;
}

@end
