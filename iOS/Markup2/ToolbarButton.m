//
//  ToolbarButton.m
//  UQMySignment
//
//  Created by simultech on 26/04/11.
//  Copyright 2011 __MyCompanyName__. All rights reserved.
//

#import "ToolbarButton.h"


@implementation ToolbarButton

@synthesize theButton;

-(id)initWithButtonImage:(NSString *)buttonImage {
    [self setButtonWithImage:buttonImage];
    self = [super initWithCustomView:theButton];
    return self;
}

-(id)initWithButtonImage:(NSString *)buttonImage target:(id)target action:(SEL)action {
    self = [self initWithButtonImage:buttonImage];
    if(self) {
        [self setTarget:target];
        [self setAction:action];
        [self updateTargetAction];
    }
    return self;
}

-(void)setSelectedImage:(NSString *)buttonOverImage {
    [theButton setImage:[UIImage imageNamed:buttonOverImage] forState:UIControlStateSelected];
}

-(void)updateTargetAction {
    if(theButton) {
        [theButton addTarget:self.target action:self.action forControlEvents:UIControlEventTouchUpInside];
    }
}

-(void)setButtonWithImage:(NSString *)buttonImage {
    UIImageView *btnIcon = [[UIImageView alloc] initWithImage:[UIImage imageNamed:buttonImage]];
    CGRect existingFrame;
    if(theButton) {
        existingFrame = [theButton frame];
    } else {
        existingFrame = [btnIcon frame];
    }
    theButton = [[UIButton alloc] initWithFrame:existingFrame];
    [theButton setShowsTouchWhenHighlighted:YES];
    [theButton setImage:[UIImage imageNamed:buttonImage] forState:UIControlStateNormal];
}

-(void)updateButtonImage:(NSString *)newButtonImage {
    [self setButtonWithImage:newButtonImage];
    [super setCustomView:theButton];
    [self updateTargetAction];
}

@end
