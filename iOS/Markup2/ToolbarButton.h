//
//  ToolbarButton.h
//  UQMySignment
//
//  Created by simultech on 26/04/11.
//  Copyright 2011 __MyCompanyName__. All rights reserved.
//

#import <Foundation/Foundation.h>


@interface ToolbarButton : UIBarButtonItem {
    UIButton* theButton;
}

@property (nonatomic,retain) UIButton *theButton;
-(id)initWithButtonImage:(NSString *)buttonImage;
-(id)initWithButtonImage:(NSString *)buttonImage target:(id)target action:(SEL)action;
-(void)setButtonWithImage:(NSString *)buttonImage;
-(void)updateButtonImage:(NSString *)newButtonImage;
-(void)updateTargetAction;
-(void)setSelectedImage:(NSString *)buttonOverImage;

@end
