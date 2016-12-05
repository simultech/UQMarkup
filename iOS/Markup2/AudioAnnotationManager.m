//
//  AudioAnnotationManager.m
//  Markup2
//

#import "AudioAnnotationManager.h"
#import <AVFoundation/AVFoundation.h>
#import "Annotation.h"
#import "Submission.h"
#import "AudioCell.h"
#import "PDFScrollView.h"

@interface AudioAnnotationManager () <AVAudioRecorderDelegate, AVAudioPlayerDelegate, NSFetchedResultsControllerDelegate>

@property (nonatomic, strong) NSMutableArray *audioAnnotations;
@property (nonatomic, strong) AVAudioRecorder *audioRecorder;
@property (nonatomic, strong) AVAudioPlayer *audioPlayer;
@property (nonatomic, strong) NSString *currentRecordingPath;

@property (nonatomic, strong) NSFetchedResultsController *fetchedResultsController;
@end

@implementation AudioAnnotationManager

static BOOL _isRecording;

- (NSFetchedResultsController *)fetchedResultsController
{
    if (!_fetchedResultsController) {
        NSPredicate *predicate = [NSPredicate predicateWithFormat:@"submission == %@ AND annotationType == %@", self.submission, @"Recording"];
        _fetchedResultsController = [Annotation fetchAllSortedBy:@"pageNumber,yPos" ascending:YES withPredicate:predicate groupBy:nil delegate:self];
    }
    
    return _fetchedResultsController;
}

- (id)initForSubmission:(Submission *)submission
{
    self = [super init];
    if (self) {
        self.submission = submission;
        _isRecording = NO;
        
        [[NSNotificationCenter defaultCenter] addObserver:self selector:@selector(playFromDocument:) name:@"playRecordedAudio" object:nil];
        [[NSNotificationCenter defaultCenter] addObserver:self selector:@selector(pauseCurrent:) name:@"pauseRecordedAudio" object:nil];
        [[NSNotificationCenter defaultCenter] addObserver:self selector:@selector(skipToPosition:) name:@"skipToRecordedAudio" object:nil];
        
    }
    return self;
}

+ (BOOL)isRecording
{
    return _isRecording;
}

- (void)newRecording
{
    [self setupForRecord];
}

- (void)setupForRecord
{
    [[NSNotificationCenter defaultCenter] postNotificationName:@"Create_Log" object:@{@"type":@"Audio",@"action":@"Record",@"value":@""}];
    NSString *tempPath = NSTemporaryDirectory();
    NSString *tempRecording = [tempPath stringByAppendingPathComponent:[NSString stringWithFormat:@"annot_%@.m4a", [Annotation timestampString]]];
    NSURL *recordingURL = [[NSURL alloc] initFileURLWithPath:tempRecording];
    self.currentRecordingPath = tempRecording;
    
    AVAudioSession *audioSession = [AVAudioSession sharedInstance];
    [audioSession setCategory:AVAudioSessionCategoryRecord error:nil];
    [audioSession setActive:YES error:nil];
    
    NSError *error;
    NSDictionary *recordSettings = @{AVFormatIDKey: @(kAudioFormatMPEG4AAC), AVSampleRateKey: @16000.0, AVLinearPCMBitDepthKey: @16, AVNumberOfChannelsKey:@1, AVLinearPCMIsBigEndianKey: @NO, AVLinearPCMIsFloatKey: @NO, AVEncoderAudioQualityKey: @(AVAudioQualityMedium)};
    self.audioRecorder = [[AVAudioRecorder alloc] initWithURL:recordingURL settings:recordSettings error:&error];
    [self.audioRecorder setDelegate:self];
    if (error) {
        DLog(@"error: %@", [error localizedDescription]);
        exit(1);
    }
    [self.audioRecorder prepareToRecord];
    _isRecording = YES;
}

- (void)startRecordingForAnnotation:(Annotation *)annotation
{
    if (!self.audioRecorder.recording) {
        self.currentAnnotation = annotation;
        [self.audioRecorder record];
        DLog(@" -- Starting recording --");
        [self.pdfViewController showRecordingImage];
    }
}

- (void)stopRecording
{
    [[NSNotificationCenter defaultCenter] postNotificationName:@"Create_Log" object:@{@"type":@"Audio",@"action":@"Finish",@"value":@""}];
    //if(self.tableView.superview) {
        [self.pdfViewController hideRecordingImage];
    //}
    if (self.audioRecorder.recording) {
        _isRecording = NO;
        
        NSTimeInterval duration = [self.audioRecorder currentTime];
        [self.audioRecorder stop];
        self.audioRecorder = nil;
        
        if(!duration) {
            [[[UIAlertView alloc]
               initWithTitle:@"Error" message:@"The audio annotation failed to save." delegate:nil
               cancelButtonTitle:@"OK" otherButtonTitles:nil]
             show];
        }
        DLog(@" -- Stopping recording --");
        [self.delegate audioManager:self didFinishRecordingToUrl:nil];
        self.currentAnnotation.localFileName = self.currentRecordingPath.lastPathComponent;
        NSString *copyPath = [self.currentAnnotation localFilePath];
        
        NSError *error;
        [[NSFileManager defaultManager] copyItemAtPath:self.currentRecordingPath toPath:copyPath error:&error];
        DLog(@"%@", error);
        [[NSManagedObjectContext defaultContext] save];
        
        
        self.currentRecordingPath = nil;
        self.currentAnnotation = nil;
        [[AVAudioSession sharedInstance] setActive:NO error:nil];
        [self.tableView reloadData];
    }
}

#pragma mark Playback methods
- (void)selectAnnotationAtIndex:(int)index
{
    NSError *error;
    AVAudioSession *session = [AVAudioSession sharedInstance];
    [session setCategory:AVAudioSessionCategoryPlayback error:nil];
    [session setActive:YES error:nil];
    NSIndexPath *ip = [NSIndexPath indexPathForRow:index inSection:0];
    Annotation *playAnnot = [self.fetchedResultsController objectAtIndexPath:ip];
    self.currentAnnotation = playAnnot;
    NSURL *playbackURL = [[NSURL alloc] initFileURLWithPath:[playAnnot localFilePath]];
    self.audioPlayer = [[AVAudioPlayer alloc] initWithContentsOfURL:playbackURL error:&error];
    if (error) {
        DLog(@"%@", error);
    }
    self.audioPlayer.delegate = self;
    [self.audioPlayer play];
}

- (Annotation *)getAnnotationAtIndex:(int)index {
    NSIndexPath *ip = [NSIndexPath indexPathForRow:index inSection:0];
    return [self.fetchedResultsController objectAtIndexPath:ip];
}

- (void)playFromDocument:(NSNotification *)not
{
    if (self.audioRecorder && self.audioRecorder.isRecording) {
        [self.pdfViewController forceStopRecordingFromClick];
        return;
    }
    
    id <NSFetchedResultsSectionInfo> sectionInfo = [[self.fetchedResultsController sections] objectAtIndex:0];
    if([sectionInfo numberOfObjects] == 0) {
        return;
    }
    for(int i = 0; i < [sectionInfo numberOfObjects]; i++) {
        NSIndexPath *ip = [NSIndexPath indexPathForRow:i inSection:0];
        Annotation *nonPlayAnnot = [self.fetchedResultsController objectAtIndexPath:ip];
        [[NSNotificationCenter defaultCenter] postNotificationName:@"setPlayingStatusNoForAnnotation" object:nonPlayAnnot];
    }
    id playAnnot = [not object];
    [[NSNotificationCenter defaultCenter] postNotificationName:@"setPlayingStatusYesForAnnotation" object:playAnnot];
    if ([self.currentAnnotation isReallyEqual:playAnnot]) {
        [self playOrPause];
        return;
    }
    AVAudioSession *session = [AVAudioSession sharedInstance];
    [session setCategory:AVAudioSessionCategoryPlayback error:nil];
    self.currentAnnotation = playAnnot;
    self.playerView.annotation = self.currentAnnotation;
    [self.playerView.annotationName setText:self.currentAnnotation.title];
    [self updateAudioTime];
    
    NSURL *playbackURL = [[NSURL alloc] initFileURLWithPath:[playAnnot localFilePath]];
    //DLog(@"%@", playbackURL);
    NSError *error;
    self.audioPlayer = nil;
    self.audioPlayer = [[AVAudioPlayer alloc] initWithContentsOfURL:playbackURL error:&error];
    if (error) {
        DLog(@"%@", error);
    }
    [self.audioPlayer setDelegate:self];
    [self playOrPause];
}

- (void) pauseCurrent:(NSNotification *)not {
    if (self.audioPlayer && [self.audioPlayer isPlaying]) {
        [self playOrPause];
    }
}

- (void)playOrPause
{
    AVAudioSession *session = [AVAudioSession sharedInstance];
    
    if (self.audioPlayer && [self.audioPlayer isPlaying]) {
        [[NSNotificationCenter defaultCenter] postNotificationName:@"Create_Log" object:@{@"type":@"Audio",@"action":@"Pause",@"value":@""}];
        [[NSNotificationCenter defaultCenter] postNotificationName:@"setPlayingStatusNoForAnnotation" object:self.currentAnnotation];
        [self.audioPlayer pause];
        [session setActive:NO error:nil];
        [self.audioPlayingTimer invalidate];
    } else {
        [[NSNotificationCenter defaultCenter] postNotificationName:@"Create_Log" object:@{@"type":@"Audio",@"action":@"Play",@"value":@""}];
        [self.audioPlayer play];
        [self.audioPlayingTimer invalidate];
        self.audioPlayingTimer = [NSTimer scheduledTimerWithTimeInterval:.01 target:self selector:@selector(updateAudioTime) userInfo:nil repeats:YES];
    }
}

- (void)updateAudioTime {
    NSString *newTime = [NSString stringWithFormat:@"%02d:%02d / %02d:%02d", (int)self.audioPlayer.currentTime / 60, (int)self.audioPlayer.currentTime % 60, (int)self.audioPlayer.duration / 60, (int)self.audioPlayer.duration % 60, nil];
    [self.playerView.timeCode setText:newTime];
    [self.playerView.progressBar setValue:((self.audioPlayer.currentTime/self.audioPlayer.duration)*100)];
}

- (void)skipToPosition:(NSNotification *)not
{
    if (self.audioPlayer) {
        NSTimeInterval positionInAudioFile = self.audioPlayer.duration * (self.playerView.progressBar.value / 100.0);
        [self.audioPlayer setCurrentTime:positionInAudioFile];
        //[self.audioPlayer play];
    } else {
        DLog(@"Can't skip to position: no audio player available.");
    }
}

#pragma mark UITableViewDelegate methods
- (void)tableView:(UITableView *)tableView didSelectRowAtIndexPath:(NSIndexPath *)indexPath
{
    Annotation *audAnn = [self getAnnotationAtIndex:indexPath.row];
    if (!self.tableView.editing) {
        [[NSNotificationCenter defaultCenter] postNotificationName:@"playRecordedAudio" object:audAnn];
        [self.pdfViewController.scrollView scrollToPage:audAnn.pageNumberValue withYOffset:audAnn.yPosValue];
    }
    else {
        NSLog(@"EDIT");
        // do want you want in edit mode
        AudioCell *cell = (AudioCell *)[tableView cellForRowAtIndexPath:indexPath];
        cell.annotationEdit = [[UITextField alloc] initWithFrame:cell.annotationName.frame];
        CGRect annotationEditFrame = cell.annotationEdit.frame;
        annotationEditFrame.origin.x = 79;
        annotationEditFrame.origin.y += 1;
        cell.annotationEdit.frame = annotationEditFrame;
        cell.annotationEdit.adjustsFontSizeToFitWidth = NO;
        cell.annotationEdit.backgroundColor = cell.annotationName.backgroundColor;
        cell.annotationEdit.font = cell.annotationName.font;
        cell.annotationEdit.autocorrectionType = UITextAutocorrectionTypeNo;
        cell.annotationEdit.autocapitalizationType = UITextAutocapitalizationTypeWords;
        [cell.annotationEdit setTextAlignment:NSTextAlignmentLeft];
        cell.annotationEdit.keyboardType = UIKeyboardTypeDefault;
        cell.annotationEdit.returnKeyType = UIReturnKeyDone;
        cell.annotationEdit.clearButtonMode = UITextFieldViewModeNever;
        cell.annotationEdit.delegate = self;
        [cell.annotationEdit setText:cell.annotationName.text];
        [cell.annotationName setHidden:YES];
        [cell addSubview:cell.annotationEdit];
        [cell.annotationEdit becomeFirstResponder];
    }
    [[tableView cellForRowAtIndexPath:indexPath] setSelected:NO];
    [tableView deselectRowAtIndexPath:indexPath animated:YES];
}

- (BOOL)textFieldShouldReturn:(UITextField *)textField {
    AudioCell *cell = (AudioCell *)textField.superview;
    [self finishEditingCell:cell];
    return YES;
}

- (void)textFieldDidEndEditing:(UITextField *)textField {
    AudioCell *cell = (AudioCell *)textField.superview;
    [self finishEditingCell:cell];
}

- (void)finishEditingCell:(AudioCell *)cell {
    if([cell isKindOfClass:[AudioCell class]]) {
        [cell.annotationName setText:cell.annotationEdit.text];
        [cell.annotationName setHidden:NO];
        [cell.annotationEdit setHidden:YES];
        NSIndexPath *indexPath = [self.tableView indexPathForCell:cell];
        Annotation *ann = [self.fetchedResultsController objectAtIndexPath:indexPath];
        [ann setTitle:cell.annotationEdit.text];
        [[NSManagedObjectContext defaultContext] save];
        [cell.annotationEdit resignFirstResponder];
        
        [self.pdfViewController softClearAnnotations];
        [self.pdfViewController insertAnnotations];
    }
}

- (void)editClicked:(id)sender {
    if([self.tableView isEditing]) {
        [self.tableView setEditing:NO animated:YES];
        [sender setTitle:@"Edit"];
        for(AudioCell *cell in [self.tableView visibleCells]) {
            if(cell.annotationEdit && [cell.annotationEdit isFirstResponder]) {
                [self finishEditingCell:cell];
                break;
            }
        }
    } else {
        [self.tableView setEditing:YES animated:YES];
        [sender setTitle:@"Done"];
    }
}

- (void)tableView:(UITableView *)tableView commitEditingStyle:(UITableViewCellEditingStyle)editingStyle forRowAtIndexPath:(NSIndexPath *)indexPath {
    if (editingStyle == UITableViewCellEditingStyleDelete) {
        Annotation *ann = [self.fetchedResultsController objectAtIndexPath:indexPath];
        //need to remove the badge
        [ann deleteEntity];
        [[NSManagedObjectContext defaultContext] save];
        [self.pdfViewController softClearAnnotations];
        [self.pdfViewController insertAnnotations];
    }
}

#pragma mark -
#pragma mark UITableViewDatasource methods
- (NSInteger)numberOfSectionsInTableView:(UITableView *)tableView
{
    return [[self.fetchedResultsController sections] count];
}

- (NSInteger)tableView:(UITableView *)tableView numberOfRowsInSection:(NSInteger)section
{
    id<NSFetchedResultsSectionInfo> sectionInfo = [[self.fetchedResultsController sections] objectAtIndex:section];
    return [sectionInfo numberOfObjects];
}

- (UITableViewCell *)tableView:(UITableView *)tableView cellForRowAtIndexPath:(NSIndexPath *)indexPath
{
    static NSString *audioCellIdentifier = @"AudioCellIdentifier";
    AudioCell *cell = [tableView dequeueReusableCellWithIdentifier:audioCellIdentifier];
    
    // Configure the cell
    Annotation *ann = [self.fetchedResultsController objectAtIndexPath:indexPath];
    cell.annotationName.text = ann.title;
    
    NSURL *playbackURL = [[NSURL alloc] initFileURLWithPath:[ann localFilePath]];
    AVAudioPlayer *tmpPlayer = [[AVAudioPlayer alloc] initWithContentsOfURL:playbackURL error:nil];
    cell.annotationDuration.text = [NSString stringWithFormat:@"%02d:%02d", (int)tmpPlayer.duration / 60, (int)tmpPlayer.duration % 60, nil];
    cell.annotationPage.text = [NSString stringWithFormat:@"Page %@",ann.pageNumber];
    return cell;
}

- (void)forceStopAudioPlayer {
    [self audioPlayerDidFinishPlaying:self.audioPlayer successfully:YES];
    [self.audioPlayingTimer invalidate];
    [self.audioPlayer stop];
    [self.audioRecorder stop];
    self.audioPlayer = nil;
    self.audioRecorder = nil;
}

- (void)dealloc
{
    [[NSNotificationCenter defaultCenter] removeObserver:self name:@"playRecordedAudio" object:nil];
    [[NSNotificationCenter defaultCenter] removeObserver:self name:@"pauseRecordedAudio" object:nil];
    [[NSNotificationCenter defaultCenter] removeObserver:self name:@"skipToRecordedAudio" object:nil];
    NSLog(@"======= GETTING RID OF AUDIO VIEW CONTROLLER ==========");
}

#pragma mark AVAudioPlayerDelegate methods
- (void)audioPlayerDidFinishPlaying:(AVAudioPlayer *)player successfully:(BOOL)flag
{
    [[NSNotificationCenter defaultCenter] postNotificationName:@"finishedPlayingRecordedAudio" object:self.currentAnnotation];
    [self updateAudioTime];
    [self.audioPlayingTimer invalidate];
}

#pragma mark NSFetchedResultsControllerDelegate
- (void)controllerWillChangeContent:(NSFetchedResultsController *)controller
{
    [self.tableView beginUpdates];
}

- (void)controller:(NSFetchedResultsController *)controller didChangeObject:(id)anObject atIndexPath:(NSIndexPath *)indexPath forChangeType:(NSFetchedResultsChangeType)type newIndexPath:(NSIndexPath *)newIndexPath
{
    switch (type) {
        case NSFetchedResultsChangeDelete:
            [self.tableView deleteRowsAtIndexPaths:@[indexPath] withRowAnimation:UITableViewRowAnimationRight];
            break;
        case NSFetchedResultsChangeInsert:
            [self.tableView insertRowsAtIndexPaths:@[newIndexPath] withRowAnimation:UITableViewRowAnimationFade];
        default:
            break;
    }
}

- (void)controllerDidChangeContent:(NSFetchedResultsController *)controller
{
    [self.tableView endUpdates];
}
@end
