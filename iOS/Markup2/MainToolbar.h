//
//  MainToolbar.h
//  UQMySignment
//
//  Created by simultech on 23/04/11.
//  Copyright 2011 __MyCompanyName__. All rights reserved.
//

#import <Foundation/Foundation.h>
#import "ToolbarButton.h"
#import "AnnotationMenu.h"
#import "PalmProtectViewController.h"

@protocol MainToolbarDelegate;
@interface MainToolbar : UIToolbar {
    NSMutableArray *itemOrder;
}

@property (nonatomic, strong) AnnotationMenu *annotationMenu;
@property (nonatomic,strong) UIBarButtonItem *titleBar;
@property (nonatomic,strong) ToolbarButton *audioButton;
@property (nonatomic,strong) UIBarButtonItem *revertButton;
@property (nonatomic, strong) UIBarButtonItem *doneButton;
@property (nonatomic, weak) IBOutlet id<MainToolbarDelegate,UIToolbarDelegate> delegate;
@property (nonatomic, strong) UIPopoverController *palmPopover;
@property (nonatomic, assign) BOOL hideEraser;
- (void)deselectButtons;
- (void)setupButtons;
- (void)setTitle:(NSString *)newTitle;
- (void)createButtonWithName:(NSString *)name withAction:(SEL)selector;
- (void)createSpaceWithWidth:(float)width withType:(NSString *)type;
- (void)drawButtons;
- (void)createSaveButtons;
- (void)setAudioButtonToRecording;
- (void)resetAudioButton;
@end

@protocol MainToolbarDelegate

- (void)mainToolbar:(MainToolbar *)toolbar didSelectAnnotationType:(AnnotationType)annotationType;
- (void)mainToolbarDidDeselectAnnotation:(MainToolbar *)toolbar;
- (void)mainToolbar:(MainToolbar *)toolbar didSetLineWidth:(CGFloat)lineWidth;
- (void)mainToolbar:(MainToolbar *)toolbar didSetStrokeColour:(UIColor *)strokeColour;
- (void)mainToolbar:(MainToolbar *)toolbar didSelectRecordToStartRecording:(BOOL)startRecording;
- (void)mainToolbarDidSelectToEnterText;
- (void)mainToolbarDidCloseDocument;

@end



