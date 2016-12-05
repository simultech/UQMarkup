//
//  AnnotationMenu.h
//  UQMySignment
//
//  Created by simultech on 5/05/11.
//  Copyright 2011 __MyCompanyName__. All rights reserved.
//

#import <UIKit/UIKit.h>
#import <QuartzCore/QuartzCore.h>
#import "PDFScrollView.h"
#import "Annotation.h"

@interface AnnotationMenu : UIView {
    UIView *buttons;
    UIView *border;
    UILabel *toolName;
    UISlider *sizeSlider;
    PDFScrollView *pdfView;
    AnnotationType annotType;
    
    UIColor * textColor;
    UIColor * penColor;
    UIColor * hightlightColor;
}
@property (nonatomic, weak) id delegate;

- (void) drawBackground;
- (void) setController:(PDFScrollView *)pdfView;
- (void) setMenu:(AnnotationType)menuType;
- (void) drawTitle:(NSString *)menuTitle;
- (void) drawAudioTitle:(NSString *)menuTitle;
- (void) setupSlider:(float)width;
- (void) setupColourPad:(NSString *)type;
- (void)setChosenColour:(UIButton *)selectedButton;

@end
