//
//  AudioPlayerCell.m
//  UQMySignment
//
//  Created by simultech on 5/07/11.
//  Copyright 2011 __MyCompanyName__. All rights reserved.
//

#import "AudioPlayerView.h"

@implementation AudioPlayerView

@synthesize annotationName,progressBar,playPauseButton,timeCode,delegate,annotationID;

- (id)initWithFrame {
    self = [super initWithFrame];
    if (self) {
        // Initialization code
        NSLog("PLEASE DO SOMETHING");
        [self setupView];
    }
    return self;
}

- (id)initWithStyle:(UITableViewCellStyle)style reuseIdentifier:(NSString *)reuseIdentifier
{
    self = [super initWithStyle:style reuseIdentifier:reuseIdentifier];
    if (self) {
        // Initialization code
        NSLog("PLEASE DO SOMETHING");
        [self setupView];
    }
    return self;
}

- (void)resetPlayerView {
    [self setInvalid];
}

- (void)setupView {
    [self setSelectionStyle:UITableViewCellSelectionStyleNone];
    self.annotationName = [[UILabel alloc] initWithFrame:CGRectMake(90,10,185,15)];
    self.annotationName.text = @"Choose an annotation";
    [self addSubview:self.annotationName];
    [self.annotationName setTextAlignment:kCTRightTextAlignment];
    [self.annotationName setBackgroundColor:[UIColor clearColor]];
    [self.annotationName setFont:[UIFont systemFontOfSize:12.0]];
    
    self.playPauseButton = [[UIButton alloc] initWithFrame:CGRectMake(20,10,61,61)];
    [self addSubview:self.playPauseButton];
    [self.playPauseButton addTarget:self action:@selector(playPauseButtonClicked:) forControlEvents:UIControlEventTouchUpInside];
    [self showPlayIcon];
    
    self.timeCode = [[UILabel alloc] initWithFrame:CGRectMake(90,55,185,15)];
    self.timeCode.text = @"00:00 / 00:00";
    [self addSubview:self.timeCode];
    [self.timeCode setTextAlignment:kCTRightTextAlignment];
    [self.timeCode setBackgroundColor:[UIColor clearColor]];
    [self.timeCode setFont:[UIFont systemFontOfSize:12.0]];
    
    self.progressBar = [[UISlider alloc] initWithFrame:CGRectMake(90, 25, 190, 30)];
    [self.progressBar addTarget:self action:@selector(progressBarValueChanged:) forControlEvents:UIControlEventValueChanged];
    [self.progressBar setMaximumValue:100.0];
    [self.progressBar setMinimumValue:0.0];
    [self addSubview:self.progressBar];
}

- (void)setInvalid {
    self.annotationName.text = @"Choose an annotation";
    self.timeCode.text = @"00:00 / 00:00";
    [self.progressBar setValue:0.0];
    self.annotationID = -1;
    self.playPauseButton.enabled = NO;
}

- (void)showPlayIcon {
    [self.playPauseButton setImage:[UIImage imageNamed:@"playbutton.png"] forState:UIControlStateNormal];
    //[self.playPauseButton setBackgroundColor:[UIColor blueColor]];
}

- (void)showPauseIcon {
    [self.playPauseButton setImage:[UIImage imageNamed:@"pausebutton.png"] forState:UIControlStateNormal];
    //[self.playPauseButton setBackgroundColor:[UIColor yellowColor]];
}

- (void)playPauseButtonClicked:(id)sel {
    if(self.annotationID != -1) {
        [delegate audioPlayerWillPlayPause];
    }
}

- (void)setSelected:(BOOL)selected animated:(BOOL)animated
{
    [super setSelected:NO animated:animated];

    // Configure the view for the selected state
}

- (void)progressBarValueChanged:(id)sel {
    [delegate audioPlayerWillSeek:progressBar.value];
}

- (void)dealloc {
    timeCode = nil;
    progressBar = nil;
    playPauseButton = nil;
    annotationName = nil;
    delegate = nil;
}

@end
