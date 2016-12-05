//
//  PalmProtectViewController.h
//  UQMySignment
//
//  Created by simultech on 12/05/11.
//  Copyright 2011 __MyCompanyName__. All rights reserved.
//

#import <UIKit/UIKit.h>
#import <QuartzCore/QuartzCore.h>

@interface PalmProtectViewController : UIViewController {
    UIButton *leftButton;
    UIButton *noButton;
    UIButton *rightButton;
}

+ (NSString *) palmProtectType;

@property (nonatomic,strong) UIButton *leftButton;
@property (nonatomic,strong) UIButton *noButton;
@property (nonatomic,strong) UIButton *rightButton;

- (void)buttonClicked:(id)sender;
- (void)getPalmType;

@end
