//
//  TextRubricCell.m
//  RubricTest
//
//  Created by Andrew Dekker on 25/08/12.
//  Copyright (c) 2012 Ably. All rights reserved.
//

#import "TextRubricCell.h"
#import <QuartzCore/QuartzCore.h>

@implementation TextRubricCell

- (id)initWithStyle:(UITableViewCellStyle)style reuseIdentifier:(NSString *)reuseIdentifier {
    self = [super initWithStyle:style reuseIdentifier:reuseIdentifier];
    if(self) {
        self.textField = [[UITextView alloc] initWithFrame:CGRectMake(0, 8, 300, 184)];
        self.textField.backgroundColor = [UIColor colorWithRed:0.98 green:0.98 blue:0.98 alpha:1];
        self.textField.layer.borderColor = [[UIColor lightGrayColor] CGColor];
        self.textField.layer.borderWidth = 1.0;
        [self.textField setDelegate:self];
        [self.textField setFont:[UIFont systemFontOfSize:18.0]];
        [self.textField setTextColor:[UIColor colorWithRed:0.176 green:0.671 blue:0.153 alpha:1]];
        [self.contentView addSubview:self.textField];
    }
    return self;
}

- (void)startValue:(NSString *)value {
    if (value) {
        [self.textField setText:value];
    }
}

- (void)loadedData {
    self.textLabel.text = [[self.data objectForKey:@"meta"] objectForKey:@"description"];
    [self.contentView bringSubviewToFront:self.textField];
}

- (void)textViewDidEndEditing:(UITextView *)textView {
    [self sendUpdatedValue:self.textField.text];
}

- (void)drawRect:(CGRect)rect {
    CGRect textFieldFrame = self.textField.frame;
    
    NSDictionary *dict = @{ NSFontAttributeName: [self.textLabel font]};
    CGSize textLabelSize = [[self.textLabel text] sizeWithAttributes:dict];
    
    
    
    textFieldFrame.size.width = self.contentView.frame.size.width - textLabelSize.width - 50;
    textFieldFrame.origin.x = self.contentView.frame.size.width - textFieldFrame.size.width - 8;
    self.textField.frame = textFieldFrame;
}

@end
