//
//  TableRubricCellColumn.m
//  RubricTest
//
//  Created by Andrew Dekker on 25/08/12.
//  Copyright (c) 2012 Ably. All rights reserved.
//

#import "TableRubricCellColumn.h"
#import <QuartzCore/QuartzCore.h>

@implementation TableRubricCellColumn

- (id)initWithFrame:(CGRect)frame
{
    self = [super initWithFrame:frame];
    if (self) {
        self.layer.borderWidth = 1;
        self.layer.borderColor = [[UIColor blackColor] CGColor];
        [self setBackgroundColor:[UIColor purpleColor]];
        
        self.nameView = [[UILabel alloc] initWithFrame:CGRectMake(0, 0, self.frame.size.width, 40)];
        [self addSubview:self.nameView];
        [self.nameView setTextAlignment:NSTextAlignmentCenter];
        [self.nameView setBackgroundColor:[UIColor darkGrayColor]];
        [self.nameView setTextColor:[UIColor whiteColor]];
        
        
        self.descriptionView = [[UITextView alloc] initWithFrame:CGRectMake(0, 40, self.frame.size.width, self.frame.size.height)];
        [self.descriptionView setBackgroundColor:[UIColor whiteColor]];
        [self.descriptionView setEditable:NO];
        [self.descriptionView setFont:[UIFont systemFontOfSize:14.0]];
        [self addSubview:self.descriptionView];
        
        self.selectImage = [[UIImageView alloc] initWithImage:[UIImage imageNamed:@"rubriccheckbox"]];
        self.selectImage.alpha = 0;
        [self addSubview:self.selectImage];
    }
    return self;
}

- (void)updatedFrame {
    CGRect nameViewFrame = self.nameView.frame;
    nameViewFrame.size.width = self.frame.size.width;
    self.nameView.frame = nameViewFrame;
    CGRect descriptionViewFrame = self.descriptionView.frame;
    descriptionViewFrame.size.width = self.frame.size.width;
    descriptionViewFrame.size.height = self.frame.size.height;
    self.descriptionView.frame = descriptionViewFrame;
    CGRect selectimageViewFrame = self.selectImage.frame;
    selectimageViewFrame.origin.x = (self.frame.size.width-selectimageViewFrame.size.width)/2+12;
    selectimageViewFrame.origin.y = (self.frame.size.height-selectimageViewFrame.size.height)/2+15;
    self.selectImage.frame = selectimageViewFrame;
}

- (void)fixCellDimensions {
    CGRect descriptionFrame = self.descriptionView.frame;
    descriptionFrame.size.width = self.frame.size.width;
    self.descriptionView.frame = descriptionFrame;
}

- (void)setCellName:(NSString *)name withDescription:(NSString *)description {
    [self.descriptionView setText:description];
    [self.nameView setText:name];
}

- (void)selectColumn:(BOOL)selected {
    self.isSelected = selected;
    if(selected) {
        self.layer.borderColor = [[UIColor colorWithRed:0.176 green:0.671 blue:0.153 alpha:1] CGColor];
        self.selectImage.transform = CGAffineTransformMakeScale(3.00, 3.00);
        [UIView beginAnimations:nil context:NULL];
        [UIView setAnimationDuration:0.5];
        [self.selectImage setAlpha:0.5];
        self.selectImage.transform = CGAffineTransformMakeScale(1.00, 1.00);
        [UIView commitAnimations];
    } else {
        self.layer.borderColor = [[UIColor blackColor] CGColor];
        self.selectImage.transform = CGAffineTransformMakeScale(1.00, 1.00);
        [self.selectImage setAlpha:0.0];
    }
}

/*
// Only override drawRect: if you perform custom drawing.
// An empty implementation adversely affects performance during animation.
- (void)drawRect:(CGRect)rect
{
    // Drawing code
}
*/

@end
