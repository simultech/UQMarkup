//
//  AudioAnnotationView.m
//  Markup2
//

#import "AudioAnnotationView.h"
#import "Annotation.h"


@implementation AudioAnnotationView {
    BOOL isPlaying;
}

- (id)initWithFrame:(CGRect)frame andAudioAnnotation:(Annotation *)annot {
    self = [super initWithFrame:frame];
    if (self) {
        self.badge = [[UIImageView alloc] initWithImage:[UIImage imageNamed:@"audio-icon_onpdf.png"]];
        [self addSubview:self.badge];
        CGRect labelFrame = CGRectMake(-35, 32, 100, 16);
        self.badgeTitle = [[UILabel alloc] initWithFrame:labelFrame];
        [self.badgeTitle setTextAlignment:NSTextAlignmentCenter];
        [self.badgeTitle setBackgroundColor:[UIColor colorWithRed:0 green:0 blue:0 alpha:0.6]];
        [self.badgeTitle setTextColor:[UIColor whiteColor]];
        [self.badgeTitle setFont:[UIFont systemFontOfSize:11.0]];
        //[self.badgeTitle.layer setCornerRadius:4.0];
        [self.badgeTitle setText:annot.title];
        [self addSubview:self.badgeTitle];
        
        // Register for taps so we can play audio
        UITapGestureRecognizer *playTap = [[UITapGestureRecognizer alloc] initWithTarget:self action:@selector(playStopAudio:)];
        [self addGestureRecognizer:playTap];
        [[NSNotificationCenter defaultCenter] addObserver:self
                                                 selector:@selector(resetBadge:)
                                                     name:@"finishedPlayingRecordedAudio"
                                                   object:nil];
        [[NSNotificationCenter defaultCenter] addObserver:self
                                                 selector:@selector(setPlayingStatusYesForAnnotation:)
                                                     name:@"setPlayingStatusYesForAnnotation"
                                                   object:nil];
        [[NSNotificationCenter defaultCenter] addObserver:self
                                                 selector:@selector(setPlayingStatusNoForAnnotation:)
                                                     name:@"setPlayingStatusNoForAnnotation"
                                                   object:nil];
        isPlaying = NO;
        _annotation = annot;
    }
    return self;
}

- (void)playStopAudio:(id)aSel
{
    NSLog(@"PLAYING OR STOPPING");
    if (!isPlaying) {
        [[NSNotificationCenter defaultCenter] postNotificationName:@"playRecordedAudio" object:self.annotation];
        isPlaying = YES;
        NSLog(@"PLAYING");
    } else {
        [[NSNotificationCenter defaultCenter] postNotificationName:@"pauseRecordedAudio" object:self.annotation];
        isPlaying = NO;
        NSLog(@"STOPPING");
    }
}

- (void)setPlayingStatusYesForAnnotation:(NSNotification *)notification {
    if([self.annotation isReallyEqual:[notification object]]) {
        isPlaying = YES;
        [self.badge setImage:[UIImage imageNamed: @"audio-icon_playing2.png"]];
    }
}

- (void)setPlayingStatusNoForAnnotation:(NSNotification *)notification {
    if([self.annotation isReallyEqual:[notification object]]) {
        isPlaying = NO;
        [self.badge setImage:[UIImage imageNamed:@"audio-icon_onpdf.png"]];
    }
}

- (void)dealloc
{
    [[NSNotificationCenter defaultCenter] removeObserver:self name:@"finishedPlayingRecordedAudio" object:nil];
    [[NSNotificationCenter defaultCenter] removeObserver:self name:@"setPlayingStatusYesForAnnotation" object:nil];
    [[NSNotificationCenter defaultCenter] removeObserver:self name:@"setPlayingStatusNoForAnnotation" object:nil];
}

- (void)resetBadge:(NSNotification *)not
{
    isPlaying = NO;
    [self.badge setImage:[UIImage imageNamed:@"audio-icon_onpdf.png"]];
}

#pragma mark UITapGestureRecogniserDelegate
- (BOOL)gestureRecognizer:(UIGestureRecognizer *)gestureRecognizer shouldRecognizeSimultaneouslyWithGestureRecognizer:(UIGestureRecognizer *)otherGestureRecognizer
{
    return YES;
}

@end
