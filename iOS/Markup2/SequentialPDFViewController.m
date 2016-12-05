//
//  SequentialPDFViewController.m
//  Markup2
//

#import "SequentialPDFViewController.h"
#import <QuartzCore/QuartzCore.h>
#import "Submission.h"
#import "PDFScrollView.h"
#import "AnnotationSettingsManager.h"
#import "AnnotationCanvasView.h"
#import "AudioAnnotationManager.h"
#import "MarkupAPIController.h"
#import "MarkupDrawerViewController.h"
#import "AudioAnnotationListViewController.h"
#import "RubricViewController.h"
#import "AnnotationLibraryViewController.h"
#import "AnnotationCanvasView.h"
#import "TiledPDFView.h"
#import "AudioPlayer.h"
#import "LibraryAnnotation.h"
#import "EditableAnnotationView.h"
#import "Log.h"

@interface SequentialPDFViewController () <MainToolbarDelegate, PDFScrollViewDelegate, AudioAnnotationmanagerDelegate>


@property (nonatomic, strong) MarkupDrawerViewController *drawer;
@property (nonatomic, strong) MarkupDrawerViewController *rubricDrawer;
@property (nonatomic, strong) MarkupDrawerViewController *libraryDrawer;

@property (nonatomic, strong) AudioAnnotationManager *audioManager;
@property (nonatomic, strong) UIImageView *recordingImage;

@property (nonatomic, strong) NSTimer *markingTimer;
@property NSTimeInterval secondsOpen;
@end

@implementation SequentialPDFViewController {
    int _currentPage;
    AnnotationType _currentAnnotationType;
}

- (id)initWithNibName:(NSString *)nibNameOrNil bundle:(NSBundle *)nibBundleOrNil
{
    self = [super initWithNibName:nibNameOrNil bundle:nibBundleOrNil];
    if (self) {
        // Custom initialization
        self.rubData = [[NSArray alloc] init];
        self.subTitle = @"Markup 2.0";
    }
    return self;
}

- (void)viewDidLoad
{
    [super viewDidLoad];
    if (UIDeviceOrientationIsLandscape([UIApplication sharedApplication].statusBarOrientation)) {
        self.loadingView = [[PDFLoadingView alloc] initWithFrame:CGRectMake(self.view.frame.size.height/2-150, self.view.frame.size.width/2-70, 300, 140)];
    } else {
        self.loadingView = [[PDFLoadingView alloc] initWithFrame:CGRectMake(self.view.frame.size.width/2-150, self.view.frame.size.height/2-70, 300, 140)];
    }
    [self.view addSubview:self.loadingView];
    if (!_currentPage) {
        _currentPage = 1;
    }
    [self setupRecordingImage];
    [self configureDrawers];
    
    [[NSNotificationCenter defaultCenter] addObserver:self
                                             selector:@selector(receiveLogNotification:)
                                                 name:@"Create_Log"
                                               object:nil];
    [self saveLogWithType:@"Automatic" andAction:@"Opened" andValue:@""];
	// Do any additional setup after loading the view.
}

- (void)receiveLogNotification:(NSNotification *)notification {
    NSDictionary *dict = notification.object;
    [self saveLogWithType:[dict objectForKey:@"type"] andAction:[dict objectForKey:@"action"] andValue:[dict objectForKey:@"value"]];
}

- (void)setupRecordingImage {
    self.recordingImage = [[UIImageView alloc] initWithImage:[UIImage imageNamed:@"recordingimage"]];
    CGRect recordingImageFrame = self.recordingImage.frame;
    if (UIDeviceOrientationIsLandscape([UIApplication sharedApplication].statusBarOrientation)) {
        recordingImageFrame.origin.y = 50;
        recordingImageFrame.origin.x = self.view.frame.size.height/2 - recordingImageFrame.size.width/2;
    } else {
        recordingImageFrame.origin.y = 50;
        recordingImageFrame.origin.x = self.view.frame.size.width/2 - recordingImageFrame.size.width/2;
    }
    self.recordingImage.frame = recordingImageFrame;
}

- (void)viewDidAppear:(BOOL)animated {
    [super viewDidAppear:animated];
    if(self.scrollView == nil) {
        [self loadPDF];
    }
    [[NSNotificationCenter defaultCenter] addObserver:self selector:@selector(placeAnnotationFromLibrary:) name:@"libraryAnnotationSelected" object:nil];
    [self.mainToolbar setTintColor:nil];
    [self.mainToolbar setTitle:self.subTitle];
    self.secondsOpen = self.submission.timeSpentMarkingValue ? self.submission.timeSpentMarkingValue : 0.0;
    self.markingTimer = [NSTimer timerWithTimeInterval:1.0 target:self selector:@selector(updateMarkingTime:) userInfo:nil repeats:YES];
    [[NSRunLoop mainRunLoop] addTimer:self.markingTimer forMode:NSDefaultRunLoopMode];
}

- (void)updateMarkingTime:(id)sender
{
    self.secondsOpen = self.secondsOpen + 1.0;
    //DLog(@"%g seconds open.", self.secondsOpen);
}

- (void)showRecordingImage {
    [self.view addSubview:self.recordingImage];
}

- (void)hideRecordingImage {
    if(self.recordingImage.superview) {
        [self.recordingImage removeFromSuperview];
    }
}

- (void)viewWillDisappear:(BOOL)animated
{
    [super viewWillDisappear:animated];
    [[NSNotificationCenter defaultCenter] removeObserver:self name:@"libraryAnnotationSelected" object:nil];
    self.submission.timeSpentMarking = @(self.secondsOpen);
    [[NSManagedObjectContext defaultContext] save];
    [self.markingTimer invalidate];
    self.markingTimer = nil;
}

- (void)didReceiveMemoryWarning
{
    [super didReceiveMemoryWarning];
    // Dispose of any resources that can be recreated.
    //self.audioManager = nil;
}

- (void)insertAnnotations
{
    for (Annotation *ann in self.submission.annotations) {
        [self.scrollView addAnnotation:ann];
        [self.scrollView setNumAnnotations:(self.scrollView.numAnnotations + 1)];
    }
}

- (void)softClearAnnotations
{
    [self.scrollView removeAllAnnotationViews];
    self.scrollView.numAnnotations = 0;
}

- (void)setSubmission:(Submission *)submission
{
    _submission = submission;
    _currentPage = 1;
}

- (void)loadPDF {
    NSData *pdfData = [NSData dataWithContentsOfFile:[_submission localPDFPath]];
    CGRect frame = CGRectMake(0.0, 44.0, self.view.frame.size.width, self.view.frame.size.height-44);
    self.scrollView = [[PDFScrollView alloc] initWithFrame:frame andPdfData:pdfData];
    self.scrollView.pdfScrollViewDelegate = self;
    [self.view addSubview:self.scrollView];
    [self.view sendSubviewToBack:self.scrollView];
    if(UIDeviceOrientationIsLandscape([UIApplication sharedApplication].statusBarOrientation)) {
        [self.scrollView resetZoomToFit];
        [self.scrollView setContentOffset:CGPointMake(0.0, 0.0) animated:NO];
    }
    self.scrollView.pageNumberView = [[PageNumberView alloc] initWithFrame:CGRectMake((self.view.frame.size.width/2)-60, self.scrollView.frame.size.height-140, 120, 40)];
    [self.view addSubview:self.scrollView.pageNumberView];
    [self.scrollView.pageNumberView setAlpha:0];
    [self.scrollView setAnnotatingEnabled:NO];
    self.scrollView.numAnnotations = 0;
    [self insertAnnotations];
    [self.loadingView fadeOut];
    self.scrollView.alpha = 0.0f;
    [UIView animateWithDuration:0.5f animations:^{
        self.scrollView.alpha = 1.0f;
    } completion:^(BOOL success){
        //Faded in
    }];
}

- (void)revertChanges:(id)sender {
    UIAlertView *alert = [[UIAlertView alloc]
                          initWithTitle: @"Clear annotations"
                          message: @"Are you sure you wish to remove all annotations?"
                          delegate: self
                          cancelButtonTitle:@"Cancel"
                          otherButtonTitles:@"Clear",nil];
    [alert show];
}

- (void)alertView:(UIAlertView *)alertView clickedButtonAtIndex:(NSInteger)buttonIndex {
	if (buttonIndex == 1) {
		DLog(@"user pressed ");
        [self.audioManager stopRecording];
        [self hideRecordingImage];
        [self removeAnnotations];
	}
}

- (void)removeAnnotations {
    [self saveLogWithType:@"Annotation" andAction:@"Clear" andValue:@""];
    [self.scrollView removeAllAnnotationViews];
    for(Annotation *ann in self.submission.annotations) {
        [ann deleteLocalFile];
        [ann deleteEntity];
    }
    [[NSManagedObjectContext defaultContext] save];
}

- (void)setRubricData:(NSArray *)rubricData {
    NSLog(@"SETTING RUBRIC DATA");
    self.rubData = rubricData;
}

- (void)setSubmissionTitle:(NSString *)submissionTitle {
    self.subTitle = submissionTitle;
}

- (void)configureDrawers
{
    NSLog(@"CREATIGN DRAWERS");
    self.drawer = [self.storyboard instantiateViewControllerWithIdentifier:@"DrawerViewController"];
    self.drawer.onLeft = YES;
    AudioAnnotationListViewController *content = [self.storyboard instantiateViewControllerWithIdentifier:@"AudioListViewController"];
    content.tableView.dataSource = self.audioManager;
    content.tableView.delegate = self.audioManager;
    content.pdfViewController = self;
    self.audioManager.tableView = content.tableView;
    [self.audioManager setPlayerView:content.player];
    self.drawer.contentViewController = content;
    [self.drawer setContentWidth:320.0];
    [self.drawer setupTabImage:@"tab_audio.png" atVerticalPosition:0.6];
    [self.view addSubview:self.drawer.view];
    [self addChildViewController:self.drawer];
    [self.drawer didMoveToParentViewController:self];
    UIBarButtonItem *editButton = content.editButtonItem;
    editButton.target = self.audioManager;
    editButton.action = @selector(editClicked:);
    [self.drawer setToolbarTitle:@"Audio Annotations" withLeftButton:nil andRightButton:editButton];
    
    
    
    self.rubricDrawer = [self.storyboard instantiateViewControllerWithIdentifier:@"DrawerViewController"];
    self.rubricDrawer.onLeft = YES;
    RubricViewController *rubricContent = [[RubricViewController alloc] initWithStyle:UITableViewStylePlain];
    rubricContent.submission = self.submission;
    self.rubricDrawer.contentViewController = rubricContent;
    [rubricContent setToolbarRef:self.rubricDrawer];
    [self.rubricDrawer setContentWidth:650.0];
    [self.rubricDrawer setupTabImage:@"tab_rubrics.png" atVerticalPosition:0.1];
    //[self.rubricDrawer.view setAlpha:0.90];
    [self.view addSubview:self.rubricDrawer.view];
    [self addChildViewController:self.rubricDrawer];
    [self.rubricDrawer didMoveToParentViewController:self];
    [self.rubricDrawer setToolbarTitle:@"Rubrics" withLeftButton:nil andRightButton:nil];
    
    [rubricContent loadData:self.rubData];
    
    self.libraryDrawer = [self.storyboard instantiateViewControllerWithIdentifier:@"DrawerViewController"];
    [self.libraryDrawer setOnLeft:NO];
    AnnotationLibraryViewController *lib = [self.storyboard instantiateViewControllerWithIdentifier:@"AnnotationLibrary"];
    [self.libraryDrawer setContentWidth:320.0];
    [self.libraryDrawer setupTabImage:@"tab_library.png" atVerticalPosition:0.1];
    [self.libraryDrawer setContentViewController:lib];
    [self.view addSubview:self.libraryDrawer.view];
    [self addChildViewController:self.libraryDrawer];
    [self.libraryDrawer didMoveToParentViewController:self];
    [self.libraryDrawer setToolbarTitle:@"Library" withLeftButton:nil andRightButton:lib.editButtonItem];
}

#pragma mark -
#pragma mark MainToolbarDelegate methods
- (void)mainToolbar:(MainToolbar *)toolbar didSelectAnnotationType:(AnnotationType)annotationType
{
    self.scrollView.readyToErase = NO;
    [self.scrollView lockZoom];
    [self.scrollView setScrollEnabled:NO];
    _currentAnnotationType = annotationType;
    AnnotationSettingsManager *annots = [AnnotationSettingsManager sharedManager];
    if (annotationType == AnnotationTypeFreehand) {
        [AnnotationCanvasView setLineWidth:[annots freehandWidth]];
        [AnnotationCanvasView setStrokeColour:[annots freehandColor]];
    } else if (annotationType == AnnotationTypeHighlight) {
        [AnnotationCanvasView setLineWidth:[annots highlightWidth]];
        [AnnotationCanvasView setStrokeColour:[annots highlightColor]];
    } else if (annotationType == AnnotationTypeErase) {
        [AnnotationCanvasView setLineWidth:[annots eraserWidth]];
        [AnnotationCanvasView setStrokeColour:[UIColor clearColor]];
        self.scrollView.readyToErase = YES;
    } else {
        // Text anotations
        [AnnotationCanvasView setStrokeColour:[UIColor clearColor]];
        [AnnotationCanvasView setLineWidth:0.0];
    }
    
    [self.scrollView setAnnotatingEnabled:YES];
}

- (void)mainToolbarDidDeselectAnnotation:(MainToolbar *)toolbar
{
    [self.scrollView unlockZoom];
    [self.scrollView setAnnotatingEnabled:NO];
    [self.scrollView setScrollEnabled:YES];
    [self saveAnnotationForDrawing];
    [self saveAnnotationForText];
    [self.scrollView setReadyToEnterText:NO];
    [self.scrollView setReadyToRecord:NO];
}

- (void)mainToolbar:(MainToolbar *)toolbar didSetLineWidth:(CGFloat)lineWidth
{
    AnnotationSettingsManager *annots = [AnnotationSettingsManager sharedManager];
    switch (_currentAnnotationType) {
        case AnnotationTypeFreehand:
            [AnnotationCanvasView setLineWidth:[annots freehandWidth]];
            break;
        case AnnotationTypeHighlight:
            [AnnotationCanvasView setLineWidth:[annots highlightWidth]];
            break;
        case AnnotationTypeErase:
            [AnnotationCanvasView setLineWidth:[annots eraserWidth]];
            break;
        default:
            break;
    }
}

- (void)mainToolbar:(MainToolbar *)toolbar didSetStrokeColour:(UIColor *)strokeColour
{
    AnnotationSettingsManager *annots = [AnnotationSettingsManager sharedManager];
    switch (_currentAnnotationType) {
        case AnnotationTypeFreehand:
            [AnnotationCanvasView setStrokeColour:[annots freehandColor]];
            break;
        case AnnotationTypeHighlight:
            [AnnotationCanvasView setStrokeColour:[annots highlightColor]];
            break;
        case AnnotationTypeText:
            [AnnotationCanvasView setStrokeColour:[annots textColor]];
            break;
        default:
            break;
    }
}

- (void)mainToolbarDidSelectToEnterText
{
    [self.scrollView setReadyToEnterText:YES];
}

- (void)mainToolbarDidCloseDocument
{
    [[NSNotificationCenter defaultCenter] removeObserver:self
                                                    name:@"Create_Log"
                                                  object:nil];
    [self saveLogWithType:@"Automatic" andAction:@"Closed" andValue:@""];
    [self.audioManager stopRecording];
    [self.delegate saveCoverImageForSubmission:self.submission];
    if (self.submission.annotations.count > 0 || self.submission.marks.count > 0) {
        [self.submission setHasLocalChangesValue:YES];
    }
    [[NSManagedObjectContext defaultContext] save];
    [self dismissViewControllerAnimated:YES completion:NULL];
}

#pragma mark Audio Annotation methods
- (AudioAnnotationManager *)audioManager
{
    if (!_audioManager) {
        _audioManager = [[AudioAnnotationManager alloc] initForSubmission:self.submission];
        _audioManager.pdfViewController = self;
        _audioManager.delegate = self;
    }
    
    return _audioManager;
}

- (void)mainToolbar:(MainToolbar *)toolbar didSelectRecordToStartRecording:(BOOL)startRecording
{
    if (startRecording) {
        [self.audioManager newRecording];
        [self.scrollView setReadyToRecord:YES];
    } else {
        [self.audioManager stopRecording];
        [self.scrollView setReadyToRecord:NO];
        [self.mainToolbar resetAudioButton];
    }
}

- (void) forceStopRecordingFromClick {
    [self mainToolbarDidDeselectAnnotation:self.mainToolbar];
    [self mainToolbar:self.mainToolbar didSelectRecordToStartRecording:NO];
}

#pragma mark PDFScrollViewDelegate methods
- (void)pdfScrollView:(PDFScrollView *)scrollView didChangeToPage:(int)pageNum
{
    [self saveLogWithType:@"Scroll" andAction:@"Page" andValue:[NSString stringWithFormat:@"%d",pageNum]];
    _currentPage = pageNum;
}

- (void)pdfScrollView:(PDFScrollView *)scrollView
 didAddAnnotationType:(AnnotationType)annotType
            withTitle:(NSString *)text
               colour:(UIColor *)colour
               onPage:(int)pageNum
               atXPos:(float)xPercentage
                 yPos:(float)yPercentage
                width:(float)widthPercentage
               height:(float)heightPercentage
{
    Annotation *annot = [Annotation createEntity];
    annot.xPos = @(xPercentage);
    annot.yPos = @(yPercentage);
    annot.pageNumber = @(pageNum);
    annot.width = @(widthPercentage);
    annot.height = @(heightPercentage);
    annot.submission = self.submission;
    NSLog(@"%@", [colour hexStringFromColor]);
    annot.colour = [colour hexStringFromColor];
    annot.title = text;
    if (annotType == AnnotationTypeRecording) {
        annot.annotationType = @"Recording";
        [self.mainToolbar setAudioButtonToRecording];
        [self.audioManager startRecordingForAnnotation:annot];
    } else if (annotType == AnnotationTypeText) {
        annot.annotationType = @"Text";
        [self.mainToolbar deselectButtons];
    }
    [self saveLogWithType:@"Annotation" andAction:@"Add" andValue:annot.annotationType];
    [[NSManagedObjectContext defaultContext] save];
    [self.scrollView addAnnotation:annot];
}

- (void)placeAnnotationFromLibrary:(NSNotification *)not
{
    TiledPDFView *pageView = [self.scrollView.pages objectAtIndex:_currentPage - 1];
    AnnotationCanvasView *canvas = pageView.annotationCanvas;
    
    
    LibraryAnnotation *libAnnot = [not object];
    Annotation *annot = [Annotation createEntity];
    annot.submission = self.submission;
    annot.annotationType = libAnnot.annotationType;
    annot.pageNumber = @(_currentPage);
    annot.title = libAnnot.title;
    annot.localFileName = libAnnot.localFileName;
    annot.colour = libAnnot.colour;
    annot.height = libAnnot.height;
    annot.width = libAnnot.width;
    float x, y;
    float h, w;
    if (![annot.annotationType isEqualToString:@"Recording"]) {
        h = libAnnot.heightValue * canvas.frame.size.height * self.scrollView.zoomScale;
        w = libAnnot.widthValue * canvas.frame.size.width * self.scrollView.zoomScale;
    } else {
        h = 30.0;
        w = 30.0;
    }
    //x = (1024.0 * 0.5); //- (w / 2.0);
    //y = (((self.scrollView.contentOffset.y / self.scrollView.zoomScale - canvas.superview.frame.origin.y)) / canvas.frame.size.height) + (((self.scrollView.frame.size.height-h)/canvas.frame.size.height)/2/self.scrollView.zoomScale);
    //x = (((self.scrollView.contentOffset.x / self.scrollView.zoomScale - canvas.superview.frame.origin.x)) / canvas.frame.size.width) + (((self.scrollView.frame.size.width-w)/canvas.frame.size.width)/2/self.scrollView.zoomScale);
    

    y = ((self.scrollView.contentOffset.y - canvas.superview.frame.origin.y * self.scrollView.zoomScale) + ((self.scrollView.frame.size.height - h ) / 2)) / (canvas.frame.size.height * self.scrollView.zoomScale);
    x = ((self.scrollView.contentOffset.x - canvas.superview.frame.origin.x * self.scrollView.zoomScale) + ((self.scrollView.frame.size.width - w ) / 2)) / (canvas.frame.size.width * self.scrollView.zoomScale);
    
    
    if(y<0) {
        y = 0;
    }
    if(y>1) {
        y = 0.5;
    }
    
    annot.xPos = @(x);
    annot.yPos = @(y);
    
    NSLog(@"annotz: %@, %@", annot.xPos, annot.yPos);
    
    NSFileManager *defaultManager = [NSFileManager defaultManager];
    NSError *copyError;
    NSString *ext = [libAnnot.localFileName pathExtension];
    NSString *filename = [NSString stringWithFormat:@"ann_%@.%@", [Annotation timestampString], ext];
    int i = 1;
    while ([defaultManager fileExistsAtPath:filename]) {
        filename = [NSString stringWithFormat:@"ann_%@_%d.%@", [LibraryAnnotation timestampString], i, ext];
        i++;
    } /* FIX */
    annot.localFileName = filename;
    
    if(![annot.annotationType isEqualToString:@"Text"]) {
        [defaultManager copyItemAtPath:[libAnnot localFilePath] toPath:[annot localFilePath] error:&copyError];
    }
    
    if (copyError) {
        DLog(@"COPY ERROR BLBK%@", copyError);
        UIAlertView *message = [[UIAlertView alloc] initWithTitle:@"Could not create annotation"
                                                          message:@"Please try again in a few seconds..."
                                                         delegate:nil
                                                cancelButtonTitle:@"OK"
                                                otherButtonTitles:nil];
        
        [message show];
    } else {
        [self.scrollView addAnnotation:annot];
    
        [[NSManagedObjectContext defaultContext] save];
        [self saveLogWithType:@"Library" andAction:@"Placed" andValue:annot.annotationType];
    }
}

#pragma mark AudioAnnotationManagerDelegate methods
- (void)audioManager:(AudioAnnotationManager *)manager didSelectAnnotation:(Annotation *)annotation
{
    int pageNum = annotation.pageNumberValue;
    double offsetY = annotation.yPosValue;
    
    CGFloat annStart = [self.scrollView getStartYOfPage:pageNum] + ([self.scrollView getHeightOfPage:pageNum] * offsetY);
    CGPoint offsetPoint = CGPointMake(self.scrollView.contentOffset.x, annStart - 100.0);
    [self.scrollView setContentOffset:offsetPoint animated:YES];
}

- (void)audioManager:(AudioAnnotationManager *)manager willStartRecordingToUrl:(NSURL *)tempUrl
{
    
}

- (void)audioManager:(AudioAnnotationManager *)manager didFinishRecordingToUrl:(NSURL *)localRecordingUrl
{
    
}

#pragma mark Annotation Baking
- (void)saveAnnotationForDrawing
{
    if (_currentAnnotationType == AnnotationTypeFreehand || _currentAnnotationType == AnnotationTypeHighlight) {
        TiledPDFView *currentPage = [self.scrollView.pages objectAtIndex:_currentPage - 1];
        AnnotationCanvasView *canvas = currentPage.annotationCanvas;
        CGFloat xPerc, yPerc, widthPerc, heightPerc;
        int pageNum;
        UIImage *annotImage = [canvas bakeSavedAnnotationWithXPerc:&xPerc yPerc:&yPerc widthPerc:&widthPerc heightPerc:&heightPerc pageNum:&pageNum];
        
        if (annotImage) {
            Annotation *imageAnnot = [Annotation createEntity];
            imageAnnot.localFileName = [NSString stringWithFormat:@"annot_%@.png", [Annotation timestampString]];
            imageAnnot.xPos = @(xPerc);
            imageAnnot.yPos = @(yPerc);
            imageAnnot.width = @(widthPerc);
            imageAnnot.height = @(heightPerc);
            imageAnnot.pageNumber = @(pageNum);
            imageAnnot.submission = self.submission;
            if (_currentAnnotationType == AnnotationTypeFreehand) {
                imageAnnot.colour = [[[AnnotationSettingsManager sharedManager] freehandColor] hexStringFromColor];
            } else {
                imageAnnot.colour = [[[AnnotationSettingsManager sharedManager] highlightColor] hexStringFromColor];
            }
            DLog(@"%@", [imageAnnot localFilePath]);
            
            NSFileManager *fileMan = [NSFileManager defaultManager];
            if (![fileMan fileExistsAtPath:[self.submission localAnnotationDirectory]]) {
                [fileMan createDirectoryAtPath:[self.submission localAnnotationDirectory] withIntermediateDirectories:YES attributes:nil error:nil];
            }
            BOOL wrote = [UIImagePNGRepresentation(annotImage) writeToFile:[imageAnnot localFilePath] atomically:YES];
            if (!wrote) {
                DLog(@"Failed to save file");
            } else {
                if (_currentAnnotationType == AnnotationTypeFreehand) {
                    imageAnnot.annotationType = @"Freehand";
                } else {
                    imageAnnot.annotationType = @"Highlight";
                }
                [[NSManagedObjectContext defaultContext] save];
                [self saveLogWithType:@"Annotation" andAction:@"Add" andValue:imageAnnot.annotationType];
            }
            
            [self.scrollView addAnnotation:imageAnnot];
        }
    }
}

- (void)saveAnnotationForText {
    if (_currentAnnotationType == AnnotationTypeText) {
        TiledPDFView *currentPage = [self.scrollView.pages objectAtIndex:_currentPage - 1];
        AnnotationCanvasView *canvas = currentPage.annotationCanvas;
        [canvas forceEndTextEditing];
    }
}

- (BOOL) saveLogWithType:(NSString *)type andAction:(NSString *)action andValue:(NSString *)value {
	Log *log = [Log createEntity];
	log.type = type;
	log.action = action;
	log.value = value;
	NSDateFormatter* formatter = [[NSDateFormatter alloc] init];
	[formatter setDateFormat:@"YYYY-MM-dd HH:mm:ss"];
	NSString* mysqlGMTString = [formatter stringFromDate:[NSDate date]];
	log.created = mysqlGMTString;
    log.submission = self.submission;
    DLog(@"!!!******* LOGGING %@",log);
	[[NSManagedObjectContext defaultContext] save];
    return YES;
}

- (void)willRotateToInterfaceOrientation:(UIInterfaceOrientation)toInterfaceOrientation duration:(NSTimeInterval)duration
{
    [self.scrollView resetZoomToFit];
}

- (void)didRotateFromInterfaceOrientation:(UIInterfaceOrientation)fromInterfaceOrientation
{
    CGRect recordingImageFrame = self.recordingImage.frame;
    recordingImageFrame.origin.y = 50;
    recordingImageFrame.origin.x = self.view.frame.size.width/2 - recordingImageFrame.size.width/2;
    self.recordingImage.frame = recordingImageFrame;
}

- (void)dealloc {
    [self.audioManager forceStopAudioPlayer];
    self.audioManager = nil;
    NSLog(@"======= GETTING RID OF PDF VIEW CONTROLLER ==========");
}


@end
