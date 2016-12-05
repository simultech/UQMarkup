//
//  TableRubricCell.h
//  RubricTest
//
//  Created by Andrew Dekker on 25/08/12.
//  Copyright (c) 2012 Ably. All rights reserved.
//

#import "RubricCell.h"

@interface TableRubricCell : RubricCell

@property (strong) NSMutableArray * columnsArray;

- (void)fixCellDimensions;

@end
