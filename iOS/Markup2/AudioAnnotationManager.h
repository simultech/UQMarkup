//
//  AudioAnnotationManager.h
//  Markup2
//

#import <Foundation/Foundation.h>
#import "AudioPlayer.h"
#import "SequentialPDFViewController.h"

@protocol AudioAnnotationmanagerDelegate;
@class Annotation, Submission;
@interface AudioAnnotationManager : NSObject <UITableViewDataSource, UITableViewDelegate, UITextFieldDelegate>
@property (nonatomic, weak) id<AudioAnnotationmanagerDelegate> delegate;
@property (nonatomic, weak) Submission *submission;
@property (nonatomic, strong) Annotation *currentAnnotation;
@property (nonatomic, strong) AudioPlayer *playerView;
@property (nonatomic, strong) NSTimer *audioPlayingTimer;
@property (nonatomic, weak) UITableView *tableView;
@property (nonatomic, weak) SequentialPDFViewController *pdfViewController;

- (id)initForSubmission:(Submission *)submission;
- (void)editClicked:(id)sender;

// Recording
- (void)newRecording;
- (void)startRecordingForAnnotation:(Annotation *)annotation;
- (void)stopRecording;
+ (BOOL)isRecording;

// Playback
- (void)playOrPause;
//- (void)selectAnnotationAtIndex:(int)index;
- (Annotation *)getAnnotationAtIndex:(int)index;
- (void)forceStopAudioPlayer;

@end

@class Annotation;
@protocol AudioAnnotationmanagerDelegate

- (void)audioManager:(AudioAnnotationManager *)manager didFinishRecordingToUrl:(NSURL *)localRecordingUrl;
- (void)audioManager:(AudioAnnotationManager *)manager willStartRecordingToUrl:(NSURL *)tempUrl;
- (void)audioManager:(AudioAnnotationManager *)manager didSelectAnnotation:(Annotation *)annotation;

@end
