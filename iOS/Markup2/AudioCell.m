//
//  AudioCell.m
//  Markup2
//

#import "AudioCell.h"
#import <QuartzCore/QuartzCore.h>

@implementation AudioCell

- (void)setupView {
    [self.annotationPage.layer setCornerRadius:12.0];
}

- (void)setSelected:(BOOL)selected animated:(BOOL)animated
{
    [super setSelected:selected animated:animated];

    // Configure the view for the selected state
}

- (void)drawRect:(CGRect)rect {
    [super drawRect:rect];
    [self setupView];
}

@end
