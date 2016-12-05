//
//  TableRubricCellColumn.h
//  RubricTest
//
//  Created by Andrew Dekker on 25/08/12.
//  Copyright (c) 2012 Ably. All rights reserved.
//

#import <UIKit/UIKit.h>

@interface TableRubricCellColumn : UIView

@property (strong) UITextView *descriptionView;
@property (strong) UILabel *nameView;
@property BOOL isSelected;
@property (strong) UIImageView *selectImage;

- (void)setCellName:(NSString *)name withDescription:(NSString *)description;
- (void)updatedFrame;
- (void)selectColumn:(BOOL)selected;
- (void)fixCellDimensions;

@end