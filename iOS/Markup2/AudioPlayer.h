//
//  AudioPlayer.h
//  Markup2
//

#import <UIKit/UIKit.h>
#import "Annotation.h"

@interface AudioPlayer : UIView

@property (strong) UILabel *annotationName;
@property (strong) UISlider *progressBar;
@property (strong) UIButton *playPauseButton;
@property (strong) UILabel *timeCode;
@property (weak) id delegate;
@property (weak) Annotation *annotation;
@property (assign) int annotationID;

- (void)resetPlayerView;
- (void)setInvalid;
-(void)setupView;
- (void)showPlayIcon;
- (void)showPauseIcon;

@end
