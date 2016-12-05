//
//  PageNumberView.h
//  UQMySignment
//
//  Created by simultech on 19/07/11.
//  Copyright 2011 __MyCompanyName__. All rights reserved.
//

#import <UIKit/UIKit.h>
#import <QuartzCore/QuartzCore.h>

@interface PageNumberView : UIView

@property (nonatomic,strong) UILabel *pageNumberText;

- (void) setup;

@end
