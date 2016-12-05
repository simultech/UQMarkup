//
//  TableRubricCell.m
//  RubricTest
//
//  Created by Andrew Dekker on 25/08/12.
//  Copyright (c) 2012 Ably. All rights reserved.
//

#import "TableRubricCell.h"
#import "TableRubricCellColumn.h"

@implementation TableRubricCell

- (id)initWithStyle:(UITableViewCellStyle)style reuseIdentifier:(NSString *)reuseIdentifier {
    self = [super initWithStyle:style reuseIdentifier:reuseIdentifier];
    if(self) {
        
    }
    return self;
}

- (void)prepareForReuse
{
    for (UIView *view in self.contentView.subviews) {
        [view removeFromSuperview];
    }
    [self.columnsArray removeAllObjects];
}

- (void)loadedData {
    
    self.columnsArray = [[NSMutableArray alloc] init];
    self.textLabel.text = [self.data objectForKey:@"name"];
    self.textLabel.frame = CGRectMake(0,0,100,30);
    NSArray *columnsData = [self.data objectForKey:@"meta"];
    int i = 0;
    int columnWidth = (self.contentView.frame.size.width/[columnsData count]);
    for(NSDictionary *columnData in columnsData) {
        TableRubricCellColumn *tmpView = [[TableRubricCellColumn alloc] initWithFrame:CGRectMake(columnWidth*i, 40, columnWidth, 100)];
        [tmpView setCellName:[columnData objectForKey:@"name"] withDescription:[columnData objectForKey:@"description"]];
        UITapGestureRecognizer *tapGesture = [[UITapGestureRecognizer alloc] initWithTarget:self action:@selector(selectedCell:)];
        [tmpView addGestureRecognizer:tapGesture];
        [self.contentView addSubview:tmpView];
        [self.columnsArray addObject:tmpView];
        i++;
    }
    [self.textLabel setHidden:YES];
    [self fixCellDimensions];
}

- (void)startValue:(NSString *)value {
    if (value) {
        int selected = [value intValue];
        [[self.columnsArray objectAtIndex:selected] selectColumn:YES];
    } else {
        for(TableRubricCellColumn *tmpView in self.columnsArray) {
            [tmpView selectColumn:NO];
        }
    }
}

- (void)selectedCell:(UIGestureRecognizer *)gesture {
    int i=0;
    int selected=0;
    for(TableRubricCellColumn *column in self.columnsArray) {
        if(gesture.view == column) {
            [column selectColumn:YES];
            selected = i;
        } else {
            [column selectColumn:NO];
        }
        i++;
    }
    [self sendUpdatedValue:[NSString stringWithFormat:@"%d",selected]];
}

- (void)fixCellDimensions {
    for(TableRubricCellColumn *column in self.columnsArray) {
        [column updatedFrame];
        [column fixCellDimensions];
    }
}


- (void)drawRect:(CGRect)rect {
    int i=0;
    int columnWidth = (self.contentView.frame.size.width/[[self.data objectForKey:@"meta"] count]);
    for(TableRubricCellColumn *column in self.columnsArray) {
        CGRect columnFrame = column.frame;
        columnFrame.size.width = columnWidth;
        columnFrame.size.height = self.contentView.frame.size.height-columnFrame.origin.y;
        columnFrame.origin.x = columnWidth*i;
        column.frame = columnFrame;
        [column updatedFrame];
        i++;
    }
}

@end
