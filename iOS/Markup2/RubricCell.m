//
//  RubricCell.m
//  RubricTest
//
//  Created by Andrew Dekker on 25/08/12.
//  Copyright (c) 2012 Ably. All rights reserved.
//

#import "RubricCell.h"

@implementation RubricCell

static int nameHeight = 20;

- (id)initWithStyle:(UITableViewCellStyle)style reuseIdentifier:(NSString *)reuseIdentifier
{
    self = [super initWithStyle:style reuseIdentifier:reuseIdentifier];
    if (self) {
        // Initialization code
    }
    return self;
}

- (void)loadData:(NSDictionary *)_newData {
    self.data = _newData;
    [self loadedData];
    [self.textLabel setBackgroundColor:[UIColor clearColor]];
    self.selectionStyle = UITableViewCellSelectionStyleNone;
}

- (void)startValue:(NSString *)value {
    
}

- (void)prepareForReuse
{
    
}

- (void)setupName:(NSString *)newName {
    self.name = [[UILabel alloc] initWithFrame:CGRectMake(10, 10, self.frame.size.width, nameHeight)];
    [self.name setBackgroundColor:[UIColor clearColor]];
    [self.name setFont:[UIFont boldSystemFontOfSize:16.0]];
    [self.name setText:newName];
    [self addSubview:self.name];
    [self.textLabel setFont:[UIFont systemFontOfSize:12.0]];
}

- (void) layoutSubviews {
    [super layoutSubviews];
    self.textLabel.frame = CGRectMake(10, 30, self.frame.size.width, 20);
}

- (void)loadedData {
    
}

- (void)sendUpdatedValue:(NSString *)value {
    if([[self delegate] respondsToSelector:@selector(updateDataWithRubricID: withValue:)]) {
        [[self delegate] updateDataWithRubricID:[self.rubricID intValue] withValue:value];
    }
}

- (void)setSelected:(BOOL)selected animated:(BOOL)animated
{
    [super setSelected:selected animated:animated];

    // Configure the view for the selected state
}

@end
