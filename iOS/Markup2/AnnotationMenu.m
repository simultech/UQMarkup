//
//  AnnotationMenu.m
//  UQMySignment
//
//  Created by simultech on 5/05/11.
//  Copyright 2011 __MyCompanyName__. All rights reserved.
//

#import "AnnotationMenu.h"
#import "AnnotationSettingsManager.h"
#import "MainToolbar.h"

@implementation AnnotationMenu

#define UIColorFromRGB(rgbValue) [UIColor colorWithRed:((float)((rgbValue & 0xFF0000) >> 16))/255.0 green:((float)((rgbValue & 0xFF00) >> 8))/255.0 blue:((float)(rgbValue & 0xFF))/255.0 alpha:0.5]

- (id)initWithFrame:(CGRect)frame
{
    self = [super initWithFrame:frame];
    if (self) {
        [self drawBackground];
        // Initialization code

    }
    return self;
}

- (void) setController:(PDFScrollView *)newPdfView {
    pdfView = newPdfView;
}

- (void)setMenu:(AnnotationType)menuType {
    annotType = menuType;
    AnnotationSettingsManager *annManager = [AnnotationSettingsManager sharedManager];
    if(menuType == AnnotationTypeText) {
        [self drawTitle:@"Text Tool (Click and drag)"];
        [self setupColourPad:@"text"];
    } else if (menuType == AnnotationTypeFreehand) {
        [self drawTitle:@"Pen"];
        [self setupColourPad:@"pen"];
        [self setupSlider:annManager.freehandWidth];
    } else if (menuType == AnnotationTypeHighlight) {
        [self drawTitle:@"Highlighter"];
        [self setupColourPad:@"highlight"];
        [self setupSlider:annManager.highlightWidth];
    } else if (menuType == AnnotationTypeErase) {
        [self drawTitle:@"Eraser"];
        [self setupSlider:annManager.eraserWidth];
    } else if (menuType == AnnotationTypeRecording) {
        [self drawAudioTitle:@"Tap anywhere in the document to begin recording"];
    }
}

- (void) drawBackground {
    [self setBackgroundColor:UIColorFromRGB(0xFFFFFF)];
    CGRect borderFrame = CGRectMake(0, self.frame.size.height-1, self.frame.size.width, 1);
    border = [[UIView alloc] initWithFrame:borderFrame];
    [border.layer setBackgroundColor:[UIColor blackColor].CGColor];
    [self addSubview:border];  
}

- (void) drawTitle:(NSString *)menuTitle {
    CGRect toolNameBorder = self.frame;
    toolNameBorder.origin.y = 0;
    toolNameBorder.size.width = toolNameBorder.size.width - 15;
    toolName = [[UILabel alloc] initWithFrame:toolNameBorder];
    [toolName setTextColor:UIColorFromRGB(0x666666)];
    [toolName setBackgroundColor:[UIColor clearColor]];
    [toolName setFont:[UIFont boldSystemFontOfSize:18.0]];
    [toolName setText:menuTitle];
    [toolName setTextAlignment:NSTextAlignmentRight];
    [self addSubview:toolName];
}

- (void) drawAudioTitle:(NSString *)menuTitle {
    CGRect toolNameBorder = self.frame;
    toolNameBorder.origin.y = 0;
    toolNameBorder.size.width = toolNameBorder.size.width;
    toolName = [[UILabel alloc] initWithFrame:toolNameBorder];
    [toolName setTextColor:UIColorFromRGB(0xFF0000)];
    [toolName setBackgroundColor:[UIColor clearColor]];
    [toolName setFont:[UIFont boldSystemFontOfSize:18.0]];
    [toolName setText:menuTitle];
    [toolName setTextAlignment:NSTextAlignmentCenter];
    [self addSubview:toolName];
}

- (void) setupSlider:(float)width {
    sizeSlider = [[UISlider alloc] initWithFrame:CGRectMake(30, 0, 140, 43)];
    if(annotType == AnnotationTypeFreehand) {
        [sizeSlider setMaximumValue:10.0];
        [sizeSlider setMinimumValue:1.0];
    } else if(annotType == AnnotationTypeHighlight) {
        [sizeSlider setMaximumValue:50.0];
        [sizeSlider setMinimumValue:5.0];
    } else if(annotType == AnnotationTypeErase) {
        [sizeSlider setMaximumValue:50.0];
        [sizeSlider setMinimumValue:7.0];
    }
    [sizeSlider setValue:width];
    [self addSubview:sizeSlider];
    [sizeSlider addTarget:self action:@selector(sliderSizeChanged:) forControlEvents:UIControlEventValueChanged];
}

- (void) setupColourPad:(NSString *)type {
    AnnotationSettingsManager *annotMan = [AnnotationSettingsManager sharedManager];
    buttons = [[UIView alloc] initWithFrame:CGRectMake(0, 0, self.frame.size.width, 30)];
    [self addSubview:buttons];
    int startPos = 190;
    BOOL setDefault = NO;
    if([type isEqualToString:@"text"]) {
        startPos = 30;
        NSArray *colours = [[NSArray alloc] initWithObjects:
                            [UIColor redColor],
                            [UIColor blueColor],
                            [UIColor greenColor],
                            [UIColor orangeColor],
                            [UIColor purpleColor],
                            [UIColor colorWithRed:0.0001 green:0.0001 blue:0.0001 alpha:1],
                            [UIColor whiteColor],
                            nil];
        for (UIColor *colour in colours) {
            UIButton *button = [[UIButton alloc] initWithFrame:CGRectMake(startPos, 6, 30, 30)];
            [button setBackgroundColor:colour];
            [button.layer setCornerRadius:15];
            [button.layer setBorderWidth:1.0];
            [button.layer setBorderColor:UIColorFromRGB(0xb7b7b7).CGColor];
            [button addTarget:self action:@selector(colorPickedText:) forControlEvents:UIControlEventTouchUpInside];
            [buttons addSubview:button];
            if(!setDefault) {
                [button.layer setBorderWidth:2.0];
                [button.layer setBorderColor:[UIColor blackColor].CGColor];
                setDefault = YES;
            }
            startPos += 40;
        }
        [annotMan setTextColor:[colours objectAtIndex:0]];
    }
    if([type isEqualToString:@"pen"]) {
        NSArray *colours = [[NSArray alloc] initWithObjects:
                            [UIColor redColor],
                            [UIColor blueColor],
                            [UIColor greenColor],
                            [UIColor orangeColor],
                            [UIColor purpleColor],
                            [UIColor colorWithRed:0.0001 green:0.0001 blue:0.0001 alpha:1],
                            [UIColor whiteColor],
                            nil];
        for (UIColor *colour in colours) {
            UIButton *button = [[UIButton alloc] initWithFrame:CGRectMake(startPos, 6, 30, 30)];
            [button setBackgroundColor:colour];
            [button.layer setCornerRadius:15];
            [button.layer setBorderWidth:1.0];
            [button.layer setBorderColor:UIColorFromRGB(0xb7b7b7).CGColor];
            [button addTarget:self action:@selector(colorPickedPen:) forControlEvents:UIControlEventTouchUpInside];
            [buttons addSubview:button];
            if(!setDefault) {
                [button.layer setBorderWidth:2.0];
                [button.layer setBorderColor:[UIColor blackColor].CGColor];
                setDefault = YES;
            }
            startPos += 40;
        }
        [annotMan setFreehandColor:[colours objectAtIndex:0]];
    }
    if([type isEqualToString:@"highlight"]) {
        NSArray *colours = [[NSArray alloc] initWithObjects:
                            UIColorFromRGB(0x3fff3f),
                            UIColorFromRGB(0xff3ff8),
                            UIColorFromRGB(0xf8ff3f),
                            UIColorFromRGB(0xff3f3f),
                            UIColorFromRGB(0x3f3fff),
                            UIColorFromRGB(0x3ffffd),
                            nil];
        for (UIColor *colour in colours) {
            UIButton *button = [[UIButton alloc] initWithFrame:CGRectMake(startPos, 6, 30, 30)];
            [button setBackgroundColor:colour];
            [button.layer setCornerRadius:15];
            [button.layer setBorderWidth:1.0];
            [button.layer setBorderColor:UIColorFromRGB(0xb7b7b7).CGColor];
            [button addTarget:self action:@selector(colorPickedHighlight:) forControlEvents:UIControlEventTouchUpInside];
            [buttons addSubview:button];
            if(!setDefault) {
                [button.layer setBorderWidth:2.0];
                [button.layer setBorderColor:[UIColor blackColor].CGColor];
                setDefault = YES;
            }
            startPos += 40;
        }
        [annotMan setHighlightColor:[colours objectAtIndex:0]];
    }
}

- (void)sliderSizeChanged:(id)sender {
    if (annotType == AnnotationTypeFreehand) {
        [[AnnotationSettingsManager sharedManager] setFreehandWidth:[(UISlider *)sender value]];
    } else if (annotType == AnnotationTypeHighlight) {
        [[AnnotationSettingsManager sharedManager] setHighlightWidth:[(UISlider *)sender value]];
    } else if (annotType == AnnotationTypeErase) {
        [[AnnotationSettingsManager sharedManager] setEraserWidth:[(UISlider *)sender value]];
    }
    
    [self.delegate mainToolbar:nil didSetLineWidth:[(UISlider *)sender value]];
}

- (void)colorPickedText:(id)sender {
    textColor = [(UIButton *)sender backgroundColor];
    [[AnnotationSettingsManager sharedManager] setTextColor:textColor];
    [self setChosenColour:(UIButton *)sender];
    [self.delegate mainToolbar:(MainToolbar *)self.superview didSetStrokeColour:textColor];
}

- (void)colorPickedPen:(id)sender {
    penColor = [(UIButton *)sender backgroundColor];
    [[AnnotationSettingsManager sharedManager] setFreehandColor:penColor];
    [self setChosenColour:(UIButton *)sender];
    [self.delegate mainToolbar:(MainToolbar *)self.superview didSetStrokeColour:textColor];
}

- (void)colorPickedHighlight:(id)sender {
    hightlightColor = [(UIButton *)sender backgroundColor];
    [[AnnotationSettingsManager sharedManager] setHighlightColor:hightlightColor];
    [self setChosenColour:(UIButton *)sender];
    [self.delegate mainToolbar:(MainToolbar *)self.superview didSetStrokeColour:textColor];
}

- (void)setChosenColour:(UIButton *)selectedButton {
    for(UIButton *button in buttons.subviews) {
        if(button == selectedButton) {
            [button.layer setBorderColor:[UIColor blackColor].CGColor];
            [button.layer setBorderWidth:2.0];
        } else {
            [button.layer setBorderColor:UIColorFromRGB(0xb7b7b7).CGColor];
            [button.layer setBorderWidth:1.0];
        }
    }
}

@end
