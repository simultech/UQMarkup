//
//  AudioPlayer.m
//  Markup2
//

#import "AudioPlayer.h"

@implementation AudioPlayer

- (id)initWithCoder:(NSCoder *)aDecoder {
    self = [super initWithCoder:aDecoder];
    if (self) {
        // Initialization code
        [self setupView];
        
        
    }
    return self;
}

- (id)initWithFrame:(CGRect)frame
{
    self = [super initWithFrame:frame];
    if (self) {
        // Initialization code
        [self setupView];
    }
    return self;
}

- (void)resetPlayerView {
    [self setInvalid];
}

- (void)setupView {
    [[NSNotificationCenter defaultCenter] addObserver:self
                                             selector:@selector(setStopped:)
                                                 name:@"finishedPlayingRecordedAudio"
                                               object:nil];
    [[NSNotificationCenter defaultCenter] addObserver:self
                                             selector:@selector(setPaused:)
                                                 name:@"setPlayingStatusYesForAnnotation"
                                               object:nil];
    [[NSNotificationCenter defaultCenter] addObserver:self
                                             selector:@selector(setPlaying:)
                                                 name:@"setPlayingStatusNoForAnnotation"
                                               object:nil];
    
    //[self setSelectionStyle:UITableViewCellSelectionStyleNone];
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

- (void)setPlaying:(NSNotification *)not {
    [self showPlayIcon];
}

- (void)setPaused:(NSNotification *)not {
    [self showPauseIcon];
}

- (void)setStopped:(NSNotification *)not {
    [self showPlayIcon];
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
    if(self.annotation) {
        [[NSNotificationCenter defaultCenter] postNotificationName:@"playRecordedAudio" object:self.annotation];
    }
}

- (void)progressBarValueChanged:(id)sel {
    //[self.delegate audioPlayerWillSeek:self.progressBar.value];
    [[NSNotificationCenter defaultCenter] postNotificationName:@"skipToRecordedAudio" object:nil];
}

- (void)dealloc {
    self.timeCode = nil;
    self.progressBar = nil;
    self.playPauseButton = nil;
    self.annotationName = nil;
    self.delegate = nil;
    [[NSNotificationCenter defaultCenter] removeObserver:self name:@"finishedPlayingRecordedAudio" object:nil];
    [[NSNotificationCenter defaultCenter] removeObserver:self name:@"setPlayingStatusYesForAnnotation" object:nil];
    [[NSNotificationCenter defaultCenter] removeObserver:self name:@"setPlayingStatusNoForAnnotation" object:nil];
}

@end
