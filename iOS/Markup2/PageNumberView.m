//
//  PageNumberView.m
//  UQMySignment
//
//  Created by simultech on 19/07/11.
//  Copyright 2011 __MyCompanyName__. All rights reserved.
//

#import "PageNumberView.h"

@implementation PageNumberView

@synthesize pageNumberText;

- (id)initWithFrame:(CGRect)frame
{
    self = [super initWithFrame:frame];
    if (self) {
        // Initialization code
        [self setup];
    }
    return self;
}

- (void) setup {
    [self setUserInteractionEnabled:NO];
    [self setBackgroundColor:[UIColor colorWithRed:0.4 green:0.4 blue:0.4 alpha:0.65]];
    self.layer.cornerRadius = 20;
    [self setAlpha:1];
    self.autoresizingMask = (
                             UIViewAutoresizingFlexibleTopMargin |
                             UIViewAutoresizingFlexibleBottomMargin |
                             UIViewAutoresizingFlexibleLeftMargin |
                             UIViewAutoresizingFlexibleRightMargin
    );
    CGRect textFrame = self.frame;
    textFrame.origin.x = 0;
    textFrame.origin.y = 0;
    self.pageNumberText = [[UILabel alloc] initWithFrame:textFrame];
    [self.pageNumberText setFont:[UIFont boldSystemFontOfSize:20.0]];
    [self.pageNumberText setTextAlignment:NSTextAlignmentCenter];
    [self.pageNumberText setShadowColor:[UIColor darkGrayColor]];
	self.pageNumberText.shadowOffset = CGSizeMake(1,1);
    [self.pageNumberText setTextColor:[UIColor whiteColor]];
    [self.pageNumberText setBackgroundColor:[UIColor clearColor]];
    [self.pageNumberText setText:@"Page 1"];
    [self addSubview:pageNumberText];
}

- (void) dealloc {
    pageNumberText = nil;
}

@end
