//
//  PublishViewController.m
//  Markup2
//

#import "PublishViewController.h"
#import "PublishController.h"
#import "Submission.h"
#import "MarkupAPIController.h"
#import "AppDelegate.h"

@interface PublishViewController ()

@property (weak, nonatomic) IBOutlet UIProgressView *publishProgress;
@property (nonatomic, strong) UIImageView *exportIcon;
@property (weak, nonatomic) IBOutlet UIImageView *backgroundView;
@property (assign) BOOL shouldCancel;
@end

@implementation PublishViewController

- (void)viewDidLoad
{
    [super viewDidLoad];
	// Do any additional setup after loading the view.
    [self setupBackground];
    
}

- (void)viewDidAppear:(BOOL)animated
{
    [super viewDidAppear:animated];
    
    [self uploadEditedSubmissions];
}

- (void)didReceiveMemoryWarning
{
    [super didReceiveMemoryWarning];
    // Dispose of any resources that can be recreated.
}

- (void)setupBackground
{
    self.exportIcon = [[UIImageView alloc] initWithImage:[UIImage imageNamed:@"fileexporticon.png"]];
    
    CGRect updatedFrame = self.exportIcon.frame;
    updatedFrame.origin.x = -250;
    updatedFrame.origin.y = 100;
    self.exportIcon.frame = updatedFrame;
    [self.backgroundView setClipsToBounds:YES];
    [self.backgroundView addSubview:self.exportIcon];

    [UIView beginAnimations:@"imageView" context:nil];
    [UIView setAnimationRepeatCount:100];
    [UIView setAnimationDelay:0];
    [UIView setAnimationDuration:2.5];
    [UIView setAnimationWillStartSelector:@selector(restartingAnimation)];
    updatedFrame = self.exportIcon.frame;
    updatedFrame.origin.x = 700;
    self.exportIcon.frame = updatedFrame;
    [UIView commitAnimations];
}

- (void)uploadEditedSubmissions
{
    AppDelegate *appDelegate = [[UIApplication sharedApplication] delegate];
    if (!appDelegate.reach.isReachable) {
        [self.delegate publishViewFailedToPublish];
        return;
    }
    DLog(@"Starting to publish");
    PublishController *pubController = [[PublishController alloc] init];
    __block NSMutableArray *zipPaths = [[NSMutableArray alloc] initWithCapacity:self.submissions.count];
    __block NSMutableArray *uploadingSubmissions = [[NSMutableArray alloc] initWithCapacity:self.submissions.count];
    __block int64_t totalBytes = 0;
    for (Submission *sub in self.submissions) {
        int64_t fileSize;
        NSString *projectZip = [pubController prepareSubmission:sub.submissionIdValue destinationFileSize:&fileSize withError:nil];
        if (projectZip) {
            [zipPaths addObject:projectZip];
            [uploadingSubmissions addObject:sub];
            DLog(@"SOMETHING WENT HORRIBLY RIGHT");
            totalBytes += fileSize;
        } else {
            DLog(@"SOMETHING WENT HORRIBLY WRONG");
        }
    }
    DLog(@"Finished zipping %d",[zipPaths count]);
    
    __block int64_t bytesWrittenSoFar = 0;
    __block NSInteger outstandingUploads = zipPaths.count;
    int i = 0;
    if([zipPaths count] == 0) {
        [self closeModal];
        return;
    }
    for (NSString *zipPath in zipPaths) {
        Submission *sub = [uploadingSubmissions objectAtIndex:i];
        int submissionId = [[sub submissionId] intValue];
        i++;
        DLog(@"Starting to upload");
        
        [[MarkupAPIController sharedApi] publishSubmissionWithId:submissionId andBundlePath:zipPath withSuccess:^{
            outstandingUploads--;
            sub.isPublished = @YES;
            DLog(@"Succeeded uploading doc");
            [[NSManagedObjectContext defaultContext] save];
            if (outstandingUploads <= 0) {
                [self closeModal];
            }
        } failure:^(NSError *error) {
            
            DLog(@"Failed uploading doc. Exiting %@",error);
            outstandingUploads--;
            [self.delegate publishViewFailedToPublish];
        } progress:^(int64_t bytesWritten, int64_t totalBytesWritten, int64_t bytesExpectedToWrite) {
            bytesWrittenSoFar += bytesWritten;
            self.publishProgress.progress = bytesWrittenSoFar / (float)totalBytes;
        }];
        
    }
}

- (void) closeModal {
    [self.delegate publishViewWillDismiss];
}

@end
