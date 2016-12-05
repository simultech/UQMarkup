//
//  BooleanRubricCell.m
//  RubricTest
//
//  Created by Andrew Dekker on 25/08/12.
//  Copyright (c) 2012 Ably. All rights reserved.
//

#import "BooleanRubricCell.h"

@implementation BooleanRubricCell

- (id)initWithStyle:(UITableViewCellStyle)style reuseIdentifier:(NSString *)reuseIdentifier {
    self = [super initWithStyle:style reuseIdentifier:reuseIdentifier];
    if(self) {
        self.boolSwitch = [[UISwitch alloc] initWithFrame:CGRectMake(0, 18, 60, 60)];
        [self.boolSwitch setOnTintColor:[UIColor colorWithRed:0.176 green:0.671 blue:0.153 alpha:1]];
        [self.contentView addSubview:self.boolSwitch];
        [self.boolSwitch addTarget:self action:@selector(switchChanged:) forControlEvents:UIControlEventValueChanged];
    }
    return self;
}

- (void)startValue:(NSString *)value {
    if([value isEqualToString:@"true"]) {
        [self.boolSwitch setOn:YES animated:YES];
    }
}

- (void)loadedData {
    self.textLabel.text = [[self.data objectForKey:@"meta"] objectForKey:@"description"];
    [self.contentView bringSubviewToFront:self.boolSwitch];
}

- (void)switchChanged:(id)theSwitch {
    NSString *boolVal = @"false";
    if(self.boolSwitch.on) {
        boolVal = @"true";
    }
    [self sendUpdatedValue:boolVal];
}

- (void)drawRect:(CGRect)rect
{
    // Drawing code
    CGRect boolSwitchFrame = self.boolSwitch.frame;
    boolSwitchFrame.origin.x = self.contentView.frame.size.width - boolSwitchFrame.size.width - 8;
    self.boolSwitch.frame = boolSwitchFrame;
}

@end
