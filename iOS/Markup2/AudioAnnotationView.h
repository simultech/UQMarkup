//
//  AudioAnnotationView.h
//  Markup2
//

#import <UIKit/UIKit.h>

@class Annotation;
@interface AudioAnnotationView : UIView <UIGestureRecognizerDelegate>
@property (nonatomic,retain) UIImageView *badge;
@property (nonatomic,retain) UILabel *badgeTitle;
@property (nonatomic, readonly) Annotation *annotation;

- (id)initWithFrame:(CGRect)frame andAudioAnnotation:(Annotation *)annot;
- (void)playStopAudio:(id)aSel;
- (void)setPlayingStatusYesForAnnotation:(Annotation *)annotation;
- (void)setPlayingStatusNoForAnnotation:(Annotation *)annotation;

@end
