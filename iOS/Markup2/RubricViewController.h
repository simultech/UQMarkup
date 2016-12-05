//
//  ViewController.h
//  RubricTest
//
//  Created by Andrew Dekker on 25/08/12.
//  Copyright (c) 2012 Ably. All rights reserved.
//

#import <UIKit/UIKit.h>
#import "RubricCell.h"
#import "Drawer.h"
#import "MarkupDrawerViewController.h"

@class Submission;
@interface RubricViewController : UITableViewController <UITableViewDataSource, UITableViewDelegate, RubricDelegate, Drawer>

@property (strong) NSMutableDictionary *data;
@property (nonatomic, weak) Submission *submission;
@property (nonatomic, weak) MarkupDrawerViewController *toolbarRef;
@property (assign) int totalMarks;

- (void)updateDataWithRubricID:(int)rubric_id withValue:(NSString *)value;
- (void)loadData:(NSArray *)newData;

@end
