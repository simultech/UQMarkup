//
//  ViewController.m
//  RubricTest
//
//  Created by Andrew Dekker on 25/08/12.
//  Copyright (c) 2012 Ably. All rights reserved.
//

#import "RubricViewController.h"
#import "BooleanRubricCell.h"
#import "NumberRubricCell.h"
#import "TextRubricCell.h"
#import <QuartzCore/QuartzCore.h>
#import "TableRubricCell.h"
#import "Rubric.h"
#import "Mark.h"
#import "Submission.h"

@interface RubricViewController ()

@end

@implementation RubricViewController

- (void)viewDidLoad
{
    self.totalMarks = 0;
    [super viewDidLoad];
    //[self loadFakeData];
    UITapGestureRecognizer *tap = [[UITapGestureRecognizer alloc] initWithTarget:self action:@selector(imageViewTapped:)];
    [self.tableView addGestureRecognizer:tap];
}

- (void)imageViewTapped:(id)something {
    [self resignFirstResponder];
    for(RubricCell *cell in self.tableView.visibleCells) {
        if([cell isKindOfClass:[TextRubricCell class]]) {
            if([[(TextRubricCell *)cell textField] isFirstResponder]) {
                [[(TextRubricCell *)cell textField] resignFirstResponder];
            }
        }
    }
}

- (void)fixCellDimensions {
    for(RubricCell *cell in self.tableView.visibleCells) {
        if([cell isKindOfClass:[TableRubricCell class]]) {
            [(TableRubricCell *)cell fixCellDimensions];
        }
    }
}

- (void)updateDataWithRubricID:(int)rubric_id withValue:(NSString *)value {
    [[NSNotificationCenter defaultCenter] postNotificationName:@"Create_Log" object:@{@"type":@"Mark",@"action":[NSString stringWithFormat:@"%d",rubric_id],@"value":value}];
    
    Mark *mark = [Mark findFirstWithPredicate:[NSPredicate predicateWithFormat:@"rubricId == %d AND SELF IN %@", rubric_id, self.submission.marks]];
    if (!mark) {
        mark = [Mark createEntity];
    }
    
    mark.rubricId = @(rubric_id);
    mark.value = value;
    mark.projectId = @1;
    mark.submission = self.submission;
    
    [[NSManagedObjectContext defaultContext] save];

    NSMutableDictionary *oldDict = [self.data mutableCopy];
    for(id sectionKey in oldDict) {
        NSMutableArray *oldArray = [[oldDict objectForKey:sectionKey] mutableCopy];
        for(NSDictionary *rubric in oldArray) {
            if([[rubric valueForKey:@"id"] isEqualToString:[NSString stringWithFormat:@"%d",rubric_id]]) {
                NSMutableDictionary *newRubric = [[NSMutableDictionary alloc] initWithDictionary:rubric];
                [newRubric setObject:value forKey:@"value"];
                NSMutableArray *sectionArray = [self.data objectForKey:sectionKey];
                int theIndex = [[self.data objectForKey:sectionKey] indexOfObject:rubric];
                [sectionArray replaceObjectAtIndex:theIndex withObject:newRubric];
                [self.data setObject:sectionArray forKey:sectionKey];
            }
        }
    }
    [self updateMarks];
    //[self.view setToolbarTitle:@"Rubrics" withLeftButton:nil andRightButton:nil];
}

- (void)updateMarks {
    float marks = 0;
    if(self.toolbarRef && self.totalMarks > 0) {
        NSLog(@"AAA %@",self.data);
        NSMutableDictionary *oldDict = [self.data mutableCopy];
        for(id sectionKey in oldDict) {
            NSMutableArray *oldArray = [[oldDict objectForKey:sectionKey] mutableCopy];
            for(NSDictionary *rubric in oldArray) {
                if([[rubric objectForKey:@"type"] isEqualToString:@"table"] && [rubric objectForKey:@"value"] != nil) {
                    int columnnumber = [[rubric objectForKey:@"value"] intValue];
                    marks += [[[[rubric objectForKey:@"meta"] objectAtIndex:columnnumber] objectForKey:@"grade"] floatValue];
                    NSLog(@"ZING ZING %@",rubric);
                }
                /*NSMutableDictionary *newRubric = [[NSMutableDictionary alloc] initWithDictionary:rubric];
                [newRubric setObject:value forKey:@"value"];
                    NSMutableArray *sectionArray = [self.data objectForKey:sectionKey];
                    int theIndex = [[self.data objectForKey:sectionKey] indexOfObject:rubric];
                    [sectionArray replaceObjectAtIndex:theIndex withObject:newRubric];
                    [self.data setObject:sectionArray forKey:sectionKey];
                }*/
            }
        }
        marks = roundf(marks);
        NSString *title = [NSString stringWithFormat:@"Rubrics (%.0f/%d)",marks,self.totalMarks];
        [self.toolbarRef setToolbarTitle:title withLeftButton:nil andRightButton:nil];
    }
}

- (void)loadData:(NSArray *)newData {
    self.data = [[NSMutableDictionary alloc] init];
    for(Rubric *rub in newData) {
        NSMutableArray *arr = [[NSMutableArray alloc] init];
        if(rub.rubricSection == nil) {
            rub.rubricSection = @"_";
        }
        if([self.data objectForKey:rub.rubricSection] != nil) {
            arr = [self.data objectForKey:rub.rubricSection];
        }
        NSMutableDictionary *obj = [[NSMutableDictionary alloc] init];
        [obj setValue:[NSString stringWithFormat:@"%d",rub.rubricId] forKey:@"id"];
        [obj setValue:rub.rubricName forKey:@"name"];
        NSString *type = @"";
        if(rub.rubricType == RubricTypeBoolean) {
            type = @"boolean";
        }
        if(rub.rubricType == RubricTypeNumber) {
            type = @"number";
        }
        if(rub.rubricType == RubricTypeText) {
            type = @"text";
        }
        if(rub.rubricType == RubricTypeTable) {
            type = @"table";
            if([[(NSArray *)rub.rubricMeta objectAtIndex:0] objectForKey:@"grade"] != nil) {
                self.totalMarks += [[[(NSArray *)rub.rubricMeta objectAtIndex:0] objectForKey:@"grade"] intValue];
            }
        }
        NSLog(@"Rubric value: %@", rub.rubricValue);
        [obj setValue:rub.rubricValue forKey:@"value"];
        [obj setValue:type forKey:@"type"];
        [obj setValue:rub.rubricSection forKey:@"section"];
        [obj setValue:rub.rubricMeta forKey:@"meta"];
        [arr addObject:obj];
        [self.data setObject:arr forKey:rub.rubricSection];
    }
    [self updateMarks];
    [self.tableView reloadData];
}

- (void)didReceiveMemoryWarning
{
    [super didReceiveMemoryWarning];
    // Dispose of any resources that can be recreated.
}

- (BOOL)shouldAutorotateToInterfaceOrientation:(UIInterfaceOrientation)interfaceOrientation
{
    if ([[UIDevice currentDevice] userInterfaceIdiom] == UIUserInterfaceIdiomPhone) {
        return (interfaceOrientation != UIInterfaceOrientationPortraitUpsideDown);
    } else {
        return YES;
    }
    [self fixCellDimensions];
}

/* Drawer delegate */
- (void)drawerWillHide
{
    
}

- (void)drawerWillShow
{
    [self fixCellDimensions];
    [self.tableView reloadData];
}

/* Tableview delegate */

- (CGFloat)tableView:(UITableView *)tableView heightForRowAtIndexPath:(NSIndexPath *)indexPath {
    NSArray *keys = [[self.data allKeys] sortedArrayUsingSelector:@selector(localizedCaseInsensitiveCompare:)];
    CGFloat height = 60;
    id aKey = [keys objectAtIndex:[indexPath section]];
    NSString *type = [[[self.data objectForKey:aKey] objectAtIndex:[indexPath row]] objectForKey:@"type"];
    if([type isEqualToString:@"text"]) {
        height = 200;
    } else if([type isEqualToString:@"table"]) {
        NSArray *meta = [[[self.data objectForKey:aKey] objectAtIndex:[indexPath row]] objectForKey:@"meta"];
        height = 40;
        /*int deviceWidth = 768;
        switch ([[UIApplication sharedApplication] statusBarOrientation]) {
            case UIInterfaceOrientationPortrait:
            case UIInterfaceOrientationPortraitUpsideDown:
                deviceWidth = 768;
                break;
            case UIInterfaceOrientationLandscapeLeft:
            case UIInterfaceOrientationLandscapeRight:
                deviceWidth = 1024;
                break;
            default:
                break;
        }*/
        int deviceWidth = self.view.bounds.size.width;
        int columnWidth = deviceWidth/[meta count];
        for(NSDictionary *columnData in meta) {
            UITextView *testText = [[UITextView alloc] initWithFrame:CGRectMake(0, 0, columnWidth+50, 10)];
            [self.view addSubview:testText];
            [testText setText:[columnData objectForKey:@"description"]];
            [testText setAlpha:0];
            [testText setFont:[UIFont systemFontOfSize:18.0]];
            CGFloat newSize = testText.contentSize.height;
            if(newSize > height) {
                height = newSize;
            }
            [testText removeFromSuperview];
        }
        height = height + 76;
        if(height < 240) {
            height = 240;
        }
    }
    height = height;
    return height;
}

- (UIView *) tableView:(UITableView *)tableView viewForHeaderInSection:(NSInteger)section
{
    UIView *headerView = [[UIView alloc] initWithFrame:CGRectMake(0, 0, tableView.bounds.size.width, 30)];
    [headerView setBackgroundColor:[UIColor lightGrayColor]];
    UILabel *headerText = [[UILabel alloc] initWithFrame:CGRectMake(10, 1, tableView.bounds.size.width, 20)];
    
    [headerText setText:[self tableView:tableView titleForHeaderInSection:section]];
    [headerText setFont:[UIFont boldSystemFontOfSize:12.0]];
    [headerText setBackgroundColor:[UIColor darkGrayColor]];
    [headerText setBackgroundColor:[UIColor clearColor]];
    [headerView addSubview:headerText];
    return headerView;
}

/* Tableview data source */

- (NSInteger)tableView:(UITableView *)tableView numberOfRowsInSection:(NSInteger)section {
    NSArray *keys = [[self.data allKeys] sortedArrayUsingSelector:@selector(localizedCaseInsensitiveCompare:)];
    id aKey = [keys objectAtIndex:section];
    return [[self.data objectForKey:aKey] count];
}

- (UITableViewCell *)tableView:(UITableView *)tableView cellForRowAtIndexPath:(NSIndexPath *)indexPath {
    static NSString *BooleanIdentifier = @"BooleanIdentifier";
    static NSString *NumberIdentifier = @"NumberIdentifier";
    static NSString *TextIdentifier = @"TextIdentifier";
    static NSString *TableIdentifier = @"TableIdentifier";
    
    NSArray *keys = [[self.data allKeys] sortedArrayUsingSelector:@selector(localizedCaseInsensitiveCompare:)];
    id aKey = [keys objectAtIndex:indexPath.section];
    NSDictionary *data = [[self.data objectForKey:aKey] objectAtIndex:indexPath.row];
    RubricCell *cell = nil;
    
    NSString *cellType = [data objectForKey:@"type"];
    if([cellType isEqualToString:@"boolean"]) {
//        cell = [tableView dequeueReusableCellWithIdentifier:BooleanIdentifier];
//        if (!cell) {
            cell = [[BooleanRubricCell alloc] initWithStyle:UITableViewCellStyleDefault reuseIdentifier:BooleanIdentifier];
//        }
    } else if([cellType isEqualToString:@"number"]) {
//        cell = [tableView dequeueReusableCellWithIdentifier:NumberIdentifier];
//        if (!cell) {
            cell = [[NumberRubricCell alloc] initWithStyle:UITableViewCellStyleDefault reuseIdentifier:NumberIdentifier];
//        }
    } else if([cellType isEqualToString:@"text"]) {
//        cell = [tableView dequeueReusableCellWithIdentifier:TextIdentifier];
//        if (!cell) {
            cell = [[TextRubricCell alloc] initWithStyle:UITableViewCellStyleDefault reuseIdentifier:TextIdentifier];
//        }
    } else {
        cell = [tableView dequeueReusableCellWithIdentifier:TableIdentifier];
//        if (!cell) {
            cell = [[TableRubricCell alloc] initWithStyle:UITableViewCellStyleDefault reuseIdentifier:TableIdentifier];
//        }
    }
    
//    [cell prepareForReuse];
    [cell loadData:data];
    
    if([data objectForKey:@"value"] != nil) {
        [cell startValue:[data objectForKey:@"value"]];
    } else {
        [cell startValue:nil];
    }
    [cell setRubricID:[data objectForKey:@"id"]];
    [cell setDelegate:self];
    [cell setupName:[data objectForKey:@"name"]];
    return cell;
}

- (NSInteger)numberOfSectionsInTableView:(UITableView *)tableView {
    return [self.data count];
}

- (NSString *)tableView:(UITableView *)tableView titleForHeaderInSection:(NSInteger)section {
    NSArray *keys = [[self.data allKeys] sortedArrayUsingSelector:@selector(localizedCaseInsensitiveCompare:)];
    return [NSString stringWithFormat:@"Section %@",[keys objectAtIndex:section]];
}

- (void)willRotateToInterfaceOrientation:(UIInterfaceOrientation)toInterfaceOrientation duration:(NSTimeInterval)duration {
    [self.view setNeedsDisplay];
}

/*- (NSString *)tableView:(UITableView *)tableView titleForFooterInSection:(NSInteger)section {
    return nil;
}*/

@end
