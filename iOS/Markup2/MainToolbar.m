//
//  MainToolbar.m
//  UQMySignment
//
//  Created by simultech on 23/04/11.
//  Copyright 2011 __MyCompanyName__. All rights reserved.
//

#import "MainToolbar.h"


@implementation MainToolbar

@synthesize titleBar,delegate,audioButton,revertButton;

- (void)awakeFromNib
{
    [self setupButtons];
    [self setupPalmPopup];
}

-(void)setTitle:(NSString *)newTitle {
    if(self.titleBar) {
        NSLog(@"SETTING TITLE TO %@",newTitle);
        if([(UIView *)[self.titleBar customView] isKindOfClass:[UILabel class]]) {
            [(UILabel *)self.titleBar.customView setText:newTitle];
        }
    }
}

-(void)deselectButtons {
    for(int i=0; i<[itemOrder count]; i++) {
        if([[itemOrder objectAtIndex:i] isKindOfClass:[ToolbarButton class]]) {
            [[(ToolbarButton *)[itemOrder objectAtIndex:i] theButton] setSelected:NO];
            [self.delegate mainToolbarDidDeselectAnnotation:self];
        }
    }
    [self hideAnnotationMenu];
}

-(void)createButtonWithName:(NSString *)name withAction:(SEL)selector {
    self.hideEraser = YES;
    ToolbarButton *tmpitem;
    NSString *imageName;
    NSString *imageOverName;
    if ([name isEqualToString:@"text"]) {
        imageName = @"text.png";
        imageOverName = @"text_over.png";
    } else if ([name isEqualToString:@"pen"]) {
        imageName = @"pencil.png";
        imageOverName = @"pencil_over.png";
    } else if ([name isEqualToString:@"highlight"]) {
        imageName = @"highlight.png";
        imageOverName = @"highlight_over.png";
    } else if ([name isEqualToString:@"eraser"]) {
        imageName = @"eraser.png";
        imageOverName = @"eraser_over.png";
    } else if ([name isEqualToString:@"palm"]) {
        imageName = @"palm.png";
        imageOverName = @"palm_over.png";
    } else if ([name isEqualToString:@"audio"]) {
        imageName = @"microphone.png";
        imageOverName = @"microphone-add_over.png";
    }
    tmpitem = [[ToolbarButton alloc] initWithButtonImage:imageName target:self action:selector];
    if(self.hideEraser && [name isEqualToString:@"eraser"]) {
        [tmpitem.theButton setHidden:YES];
    }
    if ([name isEqualToString:@"audio"]) {
        self.audioButton = tmpitem;
    }
    [tmpitem setSelectedImage:imageOverName];
    [tmpitem setWidth:44.0];
    [itemOrder addObject:tmpitem];
}

-(void)createTitleWithName:(NSString *)title {
    UILabel *tmpLabel = [[UILabel alloc] initWithFrame:CGRectMake(0, 0, 260, 40)];
    [tmpLabel setText:title];
    [tmpLabel setBackgroundColor:[UIColor clearColor]];
    [tmpLabel setTextAlignment:NSTextAlignmentCenter];
    [tmpLabel setFont:[UIFont boldSystemFontOfSize:20.0]];
    [tmpLabel setTextColor:[UIColor whiteColor]];
    titleBar = [[UIBarButtonItem alloc] initWithCustomView:tmpLabel];
    [itemOrder addObject:titleBar]; 
}

-(void)createSpaceWithWidth:(float)width withType:(NSString *)type {
    UIBarButtonItem *spacer;
    if ([type isEqualToString:@"fixed"]) {
        spacer = [[UIBarButtonItem alloc] initWithBarButtonSystemItem:UIBarButtonSystemItemFixedSpace target:nil action:nil];
        [spacer setWidth:width];
    } else {
        spacer = [[UIBarButtonItem alloc] initWithBarButtonSystemItem:UIBarButtonSystemItemFlexibleSpace target:nil action:nil];
    }
    [itemOrder addObject:spacer]; 
}

-(void)createSaveButtons {
    revertButton = [[UIBarButtonItem alloc] initWithTitle:@"Clear Annotations" style:UIBarButtonItemStyleBordered target:delegate action:@selector(revertChanges:)];
    revertButton.tintColor = [UIColor whiteColor];
    [itemOrder addObject:revertButton];
    [revertButton setEnabled:YES];
    self.doneButton = [[UIBarButtonItem alloc] initWithBarButtonSystemItem:UIBarButtonSystemItemDone target:self action:@selector(done:)];
    self.doneButton.tintColor = [UIColor whiteColor];
    [itemOrder addObject:self.doneButton];
    [self.doneButton setEnabled:YES];
    //[self.doneButton setTintColor:[UIColor blueColor]];
}

-(void)setupButtons {
    itemOrder = [[NSMutableArray alloc] init];
    NSString *existingTitle = ((UILabel *)self.titleBar.customView).text;
    if(!existingTitle || [existingTitle isEqualToString:@""]) {
        existingTitle = @"Markup";
    }
    [self createButtonWithName:@"text" withAction:@selector(openText:)];
    [self createButtonWithName:@"pen" withAction:@selector(openPen:)];
    [self createButtonWithName:@"highlight" withAction:@selector(openHighlight:)];
    [self createButtonWithName:@"eraser" withAction:@selector(openEraser:)];
    [self createButtonWithName:@"audio" withAction:@selector(openAudio:)];
    [self createSpaceWithWidth:0.0 withType:@"flexible"];
    [self createTitleWithName:existingTitle];
    [self createSpaceWithWidth:0.0 withType:@"flexible"];
    [self createButtonWithName:@"palm" withAction:@selector(openClosePalm:)];
    [self createSaveButtons];
    [self drawButtons];
}

- (void)setupPalmPopup {
    PalmProtectViewController *tmpContent = [[PalmProtectViewController alloc] init];
    UIPopoverController *tmpPop = [[UIPopoverController alloc] initWithContentViewController:tmpContent];
    [tmpContent setTitle:@"Palm Protection"];
    self.palmPopover = tmpPop;
    [self.palmPopover setPopoverContentSize:CGSizeMake(300, 100)];
}

- (void)openClosePalm:(id)sender {
    DLog(@"OPENING OR CLOSING PALM");
    if([self.palmPopover isPopoverVisible]) {
        [self.palmPopover dismissPopoverAnimated:YES];
    } else {
        [self.palmPopover setPopoverContentSize:CGSizeMake(300,40)];
        [self.palmPopover presentPopoverFromRect:[(UIButton *)sender frame] inView:self permittedArrowDirections:UIPopoverArrowDirectionUp animated:YES];
        [self.palmPopover setPopoverContentSize:CGSizeMake(300,40)];
    }
}

-(void)drawButtons {
    NSMutableArray *tmpItems = [[NSMutableArray alloc] initWithArray:itemOrder];
    if(self.hideEraser) {
        ToolbarButton *eraserButton = [tmpItems objectAtIndex:3];
        [tmpItems removeObjectAtIndex:3];
        [tmpItems insertObject:eraserButton atIndex:4];
    }
    [self setItems:(NSArray *)tmpItems animated:NO];
}

#pragma mark -
#pragma mark Toolbar button actions
- (void)toggleButton:(UIButton *)button {
    BOOL alreadyOn = [button isSelected];
    [self deselectButtons];
    if(!alreadyOn) {
        [button setSelected:YES];
        [self showAnnotationMenu];
    } else {
        [self hideAnnotationMenu];
        [self.delegate mainToolbarDidDeselectAnnotation:self];
    }
}

- (void)openText:(id)sender {
    [self toggleButton:(UIButton *)sender];
    DLog(@"Opening text tool");
    if([(UIButton *)sender isSelected]) {
        [self.delegate mainToolbar:self didSelectAnnotationType:AnnotationTypeText];
        [self.delegate mainToolbarDidSelectToEnterText];
        [self.annotationMenu setMenu:AnnotationTypeText];
    }
}

- (void)openPen:(id)sender {
    [self toggleButton:(UIButton *)sender];
    DLog(@"Opening pen tool");
    if([(UIButton *)sender isSelected]) {
        [self.delegate mainToolbar:self didSelectAnnotationType:AnnotationTypeFreehand];
        [self.annotationMenu setMenu:AnnotationTypeFreehand];
    }
}

- (void)openHighlight:(id)sender {
    [self toggleButton:(UIButton *)sender];
    DLog(@"Opening highlight tool");
    if([(UIButton *)sender isSelected]) {
        [self.delegate mainToolbar:self didSelectAnnotationType:AnnotationTypeHighlight];
        [self.annotationMenu setMenu:AnnotationTypeHighlight];
    }
}

- (void)openEraser:(id)sender {
    [self toggleButton:(UIButton *)sender];
    if([(UIButton *)sender isSelected]) {
        [self.delegate mainToolbar:self didSelectAnnotationType:AnnotationTypeErase];
        [self.annotationMenu setMenu:AnnotationTypeErase];
    }
}

- (void)openAudio:(id)sender
{
    [self toggleButton:(UIButton *)sender];
    if([(UIButton *)sender isSelected]) {
        [self.delegate mainToolbar:self didSelectAnnotationType:AnnotationTypeRecording];
        [self.delegate mainToolbar:self didSelectRecordToStartRecording:YES];
        [self.annotationMenu setMenu:AnnotationTypeRecording];
    }
}

- (void)done:(id)sender
{
    [self.delegate mainToolbarDidDeselectAnnotation:self];
    [self.delegate mainToolbarDidCloseDocument];
}

- (void)stopRecording:(id)sender
{
    if ([(UIButton *)sender isSelected]) {
        [self.delegate mainToolbar:self didSelectRecordToStartRecording:NO];
    }
    [self toggleButton:(UIButton *)sender];
}

- (void)showAnnotationMenu {
    [self hideAnnotationMenu];
    self.annotationMenu = [[AnnotationMenu alloc] initWithFrame:CGRectMake(0, 64, self.bounds.size.width, 44)];
    self.annotationMenu.delegate = self.delegate;
    [self.superview addSubview:self.annotationMenu];
}

- (void)hideAnnotationMenu {
    if([self.annotationMenu superview]) {
        [self.annotationMenu removeFromSuperview];
    }
    self.annotationMenu = nil;
}

- (void)redrawAnnotationMenu {
    if(self.annotationMenu) {
        if([self.annotationMenu superview]) {
            [self showAnnotationMenu];
        }
    }
}

- (void)setAudioButtonToRecording
{
    [self.audioButton updateButtonImage:@"microphone-recording.png"];
    [self.audioButton.theButton setSelected:YES];
    [self.audioButton.theButton removeTarget:self action:@selector(openAudio:) forControlEvents:UIControlEventTouchUpInside];
    [self.audioButton.theButton addTarget:self action:@selector(stopRecording:) forControlEvents:UIControlEventTouchUpInside];
    [self hideAnnotationMenu];
}

- (void)resetAudioButton
{
    [self.audioButton updateButtonImage:@"microphone.png"];
    [self.audioButton setSelectedImage:@"microphone-add_over.png"];
    [self.audioButton.theButton removeTarget:self action:@selector(stopRecording:) forControlEvents:UIControlEventTouchUpInside];
    [self.audioButton.theButton addTarget:self action:@selector(openAudio:) forControlEvents:UIControlEventTouchUpInside];
}



@end