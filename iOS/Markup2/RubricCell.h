//
//  RubricCell.h
//  RubricTest
//
//  Created by Andrew Dekker on 25/08/12.
//  Copyright (c) 2012 Ably. All rights reserved.
//

#import <UIKit/UIKit.h>

@protocol RubricDelegate <NSObject>
@optional
- (void)updateDataWithRubricID:(int)rubric_id withValue:(NSString *)value;
// ... other methods here
@end

@interface RubricCell : UITableViewCell

@property (strong) NSDictionary *data;
@property (strong) UILabel *name;
@property (strong) NSString *rubricID;
@property (weak) id delegate;

- (void)loadData:(NSDictionary *)_newData;
- (void)setupName:(NSString *)newName;
- (void)sendUpdatedValue:(NSString *)value;
- (void)startValue:(NSString *)value;
- (void)prepareForReuse;

@end
