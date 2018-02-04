//
//  NumberRubricCell.m
//  RubricTest
//
//  Created by Andrew Dekker on 25/08/12.
//  Copyright (c) 2012 Ably. All rights reserved.
//

#import "NumberRubricCell.h"

@implementation NumberRubricCell

- (id)initWithStyle:(UITableViewCellStyle)style reuseIdentifier:(NSString *)reuseIdentifier {
    self = [super initWithStyle:style reuseIdentifier:reuseIdentifier];
    if(self) {
        self.slider = [[UISlider alloc] initWithFrame:CGRectMake(0, 0, 200, 60)];
        [self.contentView addSubview:self.slider];
        self.value = [[UILabel alloc] initWithFrame:CGRectMake(0, 0, 60, 60)];
        [self.value setTextAlignment:NSTextAlignmentCenter];
        [self.contentView addSubview:self.value];
    }
    return self;
}

- (void)startValue:(NSString *)value {
    if (value) {
        [self.slider setValue:[value floatValue]];
        [self sliderMove:self.slider];
    }
}

- (void)loadedData {
    self.textLabel.text = [[self.data objectForKey:@"meta"] objectForKey:@"description"];
    self.slider.maximumValue = [[[self.data objectForKey:@"meta"] objectForKey:@"max"] floatValue];
    self.slider.minimumValue = [[[self.data objectForKey:@"meta"] objectForKey:@"min"] floatValue];
    [self.slider setMinimumTrackTintColor:[UIColor colorWithRed:0.176 green:0.671 blue:0.153 alpha:1]];
    
    [self.slider addTarget:self action:@selector(sliderMove:) forControlEvents:UIControlEventValueChanged];
    [self.slider addTarget:self action:@selector(sliderEnd:) forControlEvents:UIControlEventTouchUpInside];
    [self.slider addTarget:self action:@selector(sliderEnd:) forControlEvents:UIControlEventTouchUpOutside];
    
    [self.value setText:[[self.data objectForKey:@"meta"] objectForKey:@"min"]];
    [self.contentView bringSubviewToFront:self.value];
    [self.contentView bringSubviewToFront:self.slider];
}

- (void)sliderMove:(id)theSlider {
    float step = 1;
    if ([[self.data objectForKey:@"meta"] objectForKey:@"range"]) {
        step = [[[self.data objectForKey:@"meta"] objectForKey:@"range"] floatValue];
    }
    float sliderValue = self.slider.value;
    float remaining = floorf(sliderValue/step);
    sliderValue = remaining*step;
    NSString *sliderCalc = @"";
    if (step >= 1) {
        sliderCalc = [NSString stringWithFormat:@"%.0f",sliderValue];
    } else if (step >= 0.1) {
        sliderCalc = [NSString stringWithFormat:@"%.1f",sliderValue];
    } else {
        sliderCalc = [NSString stringWithFormat:@"%.2f",sliderValue];
    }
    [self.value setText:sliderCalc];
}

- (void)sliderEnd:(id)theSlider {
    float step = 1;
    if ([[self.data objectForKey:@"meta"] objectForKey:@"range"]) {
        step = [[[self.data objectForKey:@"meta"] objectForKey:@"range"] floatValue];
    }
    float sliderValue = self.slider.value;
    float remaining = floorf(sliderValue/step);
    sliderValue = remaining*step;
    NSString *sliderCalc = @"";
    if (step >= 1) {
        sliderCalc = [NSString stringWithFormat:@"%.0f",sliderValue];
    } else if (step >= 0.1) {
        sliderCalc = [NSString stringWithFormat:@"%.1f",sliderValue];
    } else {
        sliderCalc = [NSString stringWithFormat:@"%.2f",sliderValue];
    }
    [self sendUpdatedValue:sliderCalc];
}


- (void)drawRect:(CGRect)rect {
    CGRect valueFrame = self.value.frame;
    valueFrame.origin.x = self.contentView.frame.size.width - valueFrame.size.width - 8;
    self.value.frame = valueFrame;
    CGRect sliderFrame = self.slider.frame;
    sliderFrame.origin.x = valueFrame.origin.x - sliderFrame.size.width - 8;
    self.slider.frame = sliderFrame;
}

@end
