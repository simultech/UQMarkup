//
//  ProjectCollectionsViewController.m
//  SectionCollectionViewTest
//

#import "ProjectCollectionsViewController.h"
#import "SubmissionCell.h"
#import "Submission.h"
#import "MarkupAPIController.h"
#import "LoginViewController.h"
#import "UpdateViewController.h"
#import "Project.h"
#import "Submission.h"
#import "Mark.h"
#import "Log.h"
#import "SubmissionDownload.h"
#import "UIApplication+PRPNetworkActivity.h"
#import "Rubric.h"
#import "ProjectHeaderView.h"
#import "ProjectFooterView.h"
#import "PublishViewController.h"
#import <QuartzCore/QuartzCore.h>
#import "Annotation.h"
#import "AppDelegate.h"

@interface ProjectCollectionsViewController () <LoginDelegate, PublishViewControllerDelegate, UpdateDelegate>

@property (nonatomic, strong) NSMutableArray *projects;
@property (nonatomic, strong) NSMutableArray *filteredProjects;
@property (nonatomic, strong) NSMutableDictionary *frontpageThumbs;
@property (nonatomic, weak) IBOutlet UISearchBar *searchBar;

@property (nonatomic, strong) UILabel *regularTitleView;
@property (nonatomic, strong) UILabel *publishTitleView;
@property (nonatomic, strong) UIView *titleView;
@property (nonatomic, strong) UIBarButtonItem *cancelButton;
@property (nonatomic, strong) UIBarButtonItem *reloadButton;
@property (weak, nonatomic) IBOutlet UIBarButtonItem *logoutButton;
@property (weak, nonatomic) IBOutlet UIBarButtonItem *downloadAllButton;


@end

@implementation ProjectCollectionsViewController {
    BOOL _inPublishMode;
    int _getRequestsCount;
}

- (void)awakeFromNib
{
    [super awakeFromNib];
    
    self.frontpageThumbs = [[NSMutableDictionary alloc] init];
    self.isRefreshing = NO;
    _getRequestsCount = 0;

    [[NSNotificationCenter defaultCenter]  removeObserver:self name:UIKeyboardWillShowNotification object:nil];
    [[NSNotificationCenter defaultCenter]  removeObserver:self name:UIKeyboardWillHideNotification object:nil];
    [[NSNotificationCenter defaultCenter] addObserver:self selector:@selector(keyboardWillShow:) name:UIKeyboardWillShowNotification object:nil];
    [[NSNotificationCenter defaultCenter] addObserver:self selector:@selector(keyboardWillHide:) name:UIKeyboardWillHideNotification object:nil];
}

- (void)viewDidLoad
{
    [super viewDidLoad];
    self.showFilter = YES;
    self.searchText = @"";
    [self.collectionView setAllowsMultipleSelection:YES];
    self.tapToRemoveKeyboardGesture = [[UITapGestureRecognizer alloc] initWithTarget:self action:@selector(doTapToRemoveKeyboardClick:)];
    
    [self.editButtonItem setTitle:@"Publish"];
    [self.editButtonItem setAction:@selector(togglePublishMode:)];
    [self.editButtonItem setTintColor:[UIColor whiteColor]];
    [self.navigationItem setRightBarButtonItem:self.editButtonItem];

    // changed to UIBarButtonItemStylePlain
    // TODO: test, make sure didn't break
    self.cancelButton = [[UIBarButtonItem alloc] initWithTitle:@"Cancel" style:UIBarButtonItemStylePlain target:self action:@selector(togglePublishMode:)];
    self.reloadButton = self.navigationItem.leftBarButtonItem;
    
    _inPublishMode = NO;
    self.isTransitioning = NO;
    
    [self configureTitleViews];
    
    [self refreshProjects:self];
    
    UILongPressGestureRecognizer *longPress = [[UILongPressGestureRecognizer alloc] initWithTarget:self action:@selector(tryDeleteLocalSubmission:)];
    longPress.minimumPressDuration = 3;
    longPress.delegate = self;
    [self.collectionView addGestureRecognizer:longPress];
}

- (void)tryDeleteLocalSubmission:(UILongPressGestureRecognizer *)gr {
    if(!self.showingDeleteConfirm) {
        self.showingDeleteConfirm = YES;

        UIAlertController *message = [UIAlertController alertControllerWithTitle:@"Delete Local Changes?"
                                                                         message:@"Do you want to delete ALL LOCAL CHANGES for this submission?"
                                                                  preferredStyle:UIAlertControllerStyleAlert];

        UIAlertAction* cancelButton = [UIAlertAction
                                    actionWithTitle:@"Cancel"
                                    style:UIAlertActionStyleDefault
                                    handler:^(UIAlertAction * action) {
                                        self.showingDeleteConfirm = NO;
                                    }];

        UIAlertAction* deleteButton = [UIAlertAction
                                   actionWithTitle:@"Delete"
                                   style:UIAlertActionStyleDefault
                                   handler:^(UIAlertAction * action) {
                                       self.showingDeleteConfirm = NO;
                                       SubmissionCell *cell = (SubmissionCell *)[self.collectionView cellForItemAtIndexPath:self.toDeleteIndexPath];
                                       Submission *sub = cell.download.submission;
                                       for(Annotation *ann in sub.annotations) {
                                           [ann deleteLocalFile];
                                           [ann deleteEntity];
                                       }
                                       for(Log *log in sub.logs) {
                                           [log deleteEntity];
                                       }
                                       for(Mark *mark in sub.marks) {
                                           [mark deleteEntity];
                                       }
                                       [sub deleteEntity];
                                       cell.download.submission = nil;
                                       [self.collectionView reloadData];
                                   }];
        [message addAction:cancelButton];
        [message addAction:deleteButton];
        [self presentViewController:message animated:YES completion:nil];

    }
}

- (BOOL)gestureRecognizer:(UIGestureRecognizer *)gestureRecognizer shouldReceiveTouch:(UITouch *)touch {
    NSLog(@"TOUCHING");
    CGPoint touchPoint = [touch locationInView:self.collectionView];
    NSIndexPath *indexPath = [self.collectionView indexPathForItemAtPoint:touchPoint];
    if (indexPath && [gestureRecognizer isKindOfClass:[UILongPressGestureRecognizer class]])
    {
        self.toDeleteIndexPath = indexPath;
        return YES;
    }
    return NO;
}

- (void)configureTitleViews
{
    CGRect frame = CGRectMake(0.0, 0.0, 300.0, 44.0);
    self.publishTitleView = [[UILabel alloc] initWithFrame:frame];
    self.publishTitleView.backgroundColor = [UIColor clearColor];
    self.publishTitleView.text = @"Select Submissions to Publish";
    [self.publishTitleView setTextAlignment:NSTextAlignmentCenter];
    [self.publishTitleView setTextColor:[UIColor whiteColor]];
    [self.publishTitleView setFont:[UIFont boldSystemFontOfSize:20.0]];
    [self.publishTitleView setShadowColor:[UIColor colorWithRed:0.0 green:0.0 blue:0.0 alpha:0.5]];
    [self.publishTitleView setShadowOffset:CGSizeMake(0.0, -0.5)];
    self.regularTitleView = [[UILabel alloc] initWithFrame:frame];
    //self.regularTitleView.text = @"Markup";
    self.regularTitleView.text = [NSString stringWithFormat:@"UQMarkup v%@ (build %@)",[[NSBundle mainBundle] objectForInfoDictionaryKey:@"CFBundleShortVersionString"],[[[NSBundle mainBundle] infoDictionary] objectForKey:@"CFBundleVersion"]];
    self.regularTitleView.backgroundColor = [UIColor clearColor];
    [self.regularTitleView setFont:[UIFont boldSystemFontOfSize:20.0]];
    [self.regularTitleView setTextAlignment:NSTextAlignmentCenter];
    [self.regularTitleView setShadowColor:[UIColor colorWithRed:0.0 green:0.0 blue:0.0 alpha:0.5]];
    [self.regularTitleView setShadowOffset:CGSizeMake(0.0, -0.5)];
    [self.regularTitleView setTextColor:[UIColor whiteColor]];
    self.titleView = [[UIView alloc] initWithFrame:frame];
    self.titleView.backgroundColor = [UIColor clearColor];
    self.titleView.opaque = NO;
    [self.titleView addSubview:self.publishTitleView];
    [self.titleView addSubview:self.regularTitleView];
    [self.publishTitleView setAlpha:0.0];
    self.navigationItem.titleView = self.titleView;
    [self refreshProjects:self];
}

- (void)doTapToRemoveKeyboardClick:(id)target {
    [self.searchBar resignFirstResponder];
}

- (void)viewDidAppear:(BOOL)animated
{
    [super viewDidAppear:animated];
    self.isTransitioning = NO;
    if([self.collectionView.visibleCells count] == 0) {
        [self refreshProjects:self];
    } else {
        [self backupCoreData];
    }
    [self checkForUpdate];
    [super viewDidAppear:animated];
}

- (void)backupCoreData {
    NSString *supportPath;
    NSArray *paths = NSSearchPathForDirectoriesInDomains(NSApplicationSupportDirectory, NSUserDomainMask, YES);
    if (paths.count > 0) {
        NSDateFormatter *format = [[NSDateFormatter alloc] init];
        [format setDateFormat:@"dd-HH-mm"];
        supportPath = [paths objectAtIndex:0];
        NSString *dbPath = [[supportPath stringByAppendingPathComponent:@"Markup"] stringByAppendingPathComponent: @"Markup.sqlite"];
        NSDate *date = [[NSDate alloc] init];
        NSString *dateString = [format stringFromDate:date];
        NSFileManager *fileManager= [NSFileManager defaultManager];
        NSString *backupPath = [supportPath stringByAppendingPathComponent:@"Backups"];
        BOOL isDir;
        if(![fileManager fileExistsAtPath:backupPath isDirectory:&isDir]) {
            [fileManager createDirectoryAtPath:backupPath withIntermediateDirectories:YES attributes:nil error:nil];
        }
        NSString *fileName = [NSString stringWithFormat:@"Markup_Backup_%@.sqlite",dateString];
        NSString *backupFile = [backupPath stringByAppendingPathComponent:fileName];
        if ([fileManager fileExistsAtPath:backupFile] == YES) {
            [fileManager removeItemAtPath:backupFile error:nil];
        }
        [[NSFileManager defaultManager] copyItemAtPath:dbPath toPath:backupFile error:nil];
    }
}

- (void)didReceiveMemoryWarning
{
    [super didReceiveMemoryWarning];
    // Dispose of any resources that can be recreated.
    [self.frontpageThumbs removeAllObjects];
}

- (void)prepareForSegue:(UIStoryboardSegue *)segue sender:(id)sender
{
    if ([segue.identifier isEqualToString:@"Open Submission"]) {
        Submission *sub = [[(SubmissionCell *)sender download] submission];
        [[[segue.destinationViewController viewControllers] objectAtIndex:0] setSubmission:sub];
        [[[segue.destinationViewController viewControllers] objectAtIndex:0] setSubmissionTitle:[[(SubmissionCell *)sender download] title]];
        [[[segue.destinationViewController viewControllers] objectAtIndex:0] setDelegate:self];
        
        NSArray *rubrics = [[NSArray alloc] initWithArray:[[[(SubmissionCell *)sender download] project] rubrics] copyItems:YES];
        for (Rubric *rubric in rubrics) {
            Mark *mark = [Mark findFirstWithPredicate:[NSPredicate predicateWithFormat:@"rubricId == %d AND SELF IN %@", rubric.rubricId, sub.marks]];
            if (mark) {
                rubric.rubricValue = mark.value;
            }
        }
        [[[segue.destinationViewController viewControllers] objectAtIndex:0] setRubricData:rubrics];
        [[[segue.destinationViewController viewControllers] objectAtIndex:0] setTitle:sub.localDirectoryName.stringByDeletingPathExtension];
    } else if ([segue.identifier isEqualToString:@"Show Login"]) {
        [[[segue.destinationViewController viewControllers] objectAtIndex:0] setDelegate:self];
    } else if ([segue.identifier isEqualToString:@"Publish Selected Files"]) {
        NSMutableArray *uploads = [[NSMutableArray alloc] init];
        for (NSIndexPath *path in [self.collectionView indexPathsForSelectedItems]) {
            SubmissionCell *cell = (SubmissionCell *)[self collectionView:self.collectionView cellForItemAtIndexPath:path];
            [uploads addObject:cell.download.submission];
        }
        [[[segue.destinationViewController viewControllers] objectAtIndex:0] setSubmissions:uploads];
        [[[segue.destinationViewController viewControllers] objectAtIndex:0] setDelegate:self];
    } else if ([segue.identifier isEqualToString:@"Show Update"]) {
        [segue.destinationViewController setDelegate:self];
    }
}

- (void)checkForUpdate {
    AppDelegate *appDelegate = (AppDelegate*)[[UIApplication sharedApplication] delegate];
    
    if (appDelegate.reach.isReachable) {
        [[MarkupAPIController sharedApi] isLatestVersionWithSuccess:^(NSDictionary *latestInfo) {
            if([[latestInfo objectForKey:@"latest"] isEqualToString:@"false"]) {
                self.versionInfo = latestInfo;
                [self performSegueWithIdentifier:@"Show Update" sender:self];
            }
        } andFailure:^(NSError *error) {
            NSLog(@"Couldnt get version details, ignoring %@",error);
        }];
    }
}

- (IBAction)refreshProjects:(id)sender
{

    AppDelegate *appDelegate = (AppDelegate*)[[UIApplication sharedApplication] delegate];
    DLog(@"refreshProjects %d", appDelegate.reach.isReachable);
    if (appDelegate.reach.isReachable) {
        if(self.isRefreshing) {
            return;
        }
        self.isRefreshing = YES;
        [[UIApplication sharedApplication] prp_pushNetworkActivity];
        [[MarkupAPIController sharedApi] getMyProjectListWithSuccess:^(NSArray *projects) {
            [[UIApplication sharedApplication] prp_popNetworkActivity];
            self.projects = [projects mutableCopy];
            for(int i=[self.projects count]-1; i>=0; i--) {
                if([[[self.projects objectAtIndex:i] submissions] count] == 0) {
                    [self.projects removeObjectAtIndex:i];
                }
            }
            [self updateFilter];
            NSData *archiveData = [NSKeyedArchiver archivedDataWithRootObject:self.projects];
            [archiveData writeToFile:[self localProjectsFile] atomically:YES];
            self.isRefreshing = NO;
            _getRequestsCount = 0;
        } orFailure:^(NSError *error) {
            [[UIApplication sharedApplication] prp_popNetworkActivity];
            if ([error code] == 403) {
                NSUserDefaults *defaults = [NSUserDefaults standardUserDefaults];
                if ([defaults objectForKey:@"username"]) {
                    NSString *username = [defaults objectForKey:@"username"];
                    NSString *password = [defaults objectForKey:@"password"];
                    [[MarkupAPIController sharedApi] loginWithUsername:username andPassword:password withSucess:^{
                        if (_getRequestsCount < 3) {
                            [self refreshProjects:self];
                            _getRequestsCount++;
                        } else {
                            [self performSegueWithIdentifier:@"Show Login" sender:self];
                            _getRequestsCount = 0;
                        }
                    } andFailure:^(NSError *error) {
                        [self performSegueWithIdentifier:@"Show Login" sender:self];
                        _getRequestsCount = 0;
                    }];
                } else {
                    [self performSegueWithIdentifier:@"Show Login" sender:self];
                    _getRequestsCount = 0;
                }
            }
            self.isRefreshing = NO;
        }];
    } else {
        [self displayLocalProjects];
    }
}

- (void)displayLocalProjects
{

    self.projects = [NSKeyedUnarchiver unarchiveObjectWithFile:[self localProjectsFile]];
    if (!self.projects) {
        UIAlertController *noProjects = [UIAlertController alertControllerWithTitle:@"No projects"
                                                                         message:@"You must be connected to the internet at least once to download projects. Try refreshing."
                                                                  preferredStyle:UIAlertControllerStyleAlert];

        UIAlertAction* cancelButton = [UIAlertAction
                                       actionWithTitle:@"Ok"
                                       style:UIAlertActionStyleDefault
                                       handler:^(UIAlertAction * action) {}];
        [noProjects addAction:cancelButton];
        [self presentViewController:noProjects animated:YES completion:nil];
        return;
    }
    [self updateFilter];
}

- (NSString *)localProjectsFile
{
    NSString *supportPath;
    NSArray *paths = NSSearchPathForDirectoriesInDomains(NSApplicationSupportDirectory, NSUserDomainMask, YES);
    if (paths.count > 0) {
        supportPath = [paths objectAtIndex:0];
    }
    return [supportPath stringByAppendingPathComponent:@"localProjects.plist"];
}

- (IBAction)togglePublishMode:(id)sender
{
    if (_inPublishMode) {
        [self.navigationController.navigationBar setTranslucent:YES];
        [self.navigationController.toolbar setTranslucent:YES];
    } else {
        [self.navigationController.navigationBar setTranslucent:NO];
        [self.navigationController.toolbar setTranslucent:NO];
    }
    [UIView beginAnimations:nil context:nil];
    [UIView setAnimationDuration:0.2];
    [UIView setAnimationCurve:UIViewAnimationCurveEaseInOut];
    if (_inPublishMode) {
        [self.editButtonItem setEnabled:YES];
        [self.navigationItem setLeftBarButtonItem:self.reloadButton animated:YES];
        [self.editButtonItem setAction:@selector(togglePublishMode:)];
        [self.navigationItem setRightBarButtonItem:self.editButtonItem];
        [self.editButtonItem setTitle:@"Publish"];
        [self.publishTitleView setAlpha:0.0];
        [self.regularTitleView setAlpha:1.0];
        
        [self.navigationController.navigationBar setBarTintColor:[UIColor colorWithHue:0.729 saturation:0.550 brightness:0.663 alpha:1]];
        [self.navigationController.toolbar setBarTintColor:[UIColor colorWithHue:0.729 saturation:0.550 brightness:0.663 alpha:1]];
        [self.navigationController.toolbar setTintColor:[UIColor whiteColor]];
        [self.editButtonItem setTintColor:[UIColor whiteColor]];
        
        for(int i=0; i<[self.filteredProjects count]; i++) {
            for(int j=0; j<[[[self.filteredProjects objectAtIndex:i] submissions] count]; j++) {
                [self.collectionView deselectItemAtIndexPath:[NSIndexPath indexPathForItem:j inSection:i] animated:NO];
            }
        }
    } else {
        [self.editButtonItem setAction:@selector(publishSelectedSubmissions)];
        [self.navigationItem setRightBarButtonItem:self.editButtonItem];
        [self.navigationItem setLeftBarButtonItem:self.cancelButton animated:YES];
        [self.editButtonItem setTitle:@"Upload"];
        [self.navigationItem setTitle:@"Select Submissions to Publish"];
        [self.publishTitleView setAlpha:1.0];
        [self.regularTitleView setAlpha:0.0];
        
        
        [self.navigationController.navigationBar setBarTintColor:UIColorFromRGB(0xE29C40)];
        [self.navigationController.navigationBar setTintColor:[UIColor whiteColor]];
        [self.navigationController.toolbar setBarTintColor:UIColorFromRGB(0xE29C40)];
        
        
        [self updatePublishButtonText];
    }
    
    [UIView commitAnimations];
    
    _inPublishMode = !_inPublishMode;
}

- (void)publishSelectedSubmissions
{
    NSLog(@"PUBLISHING");
    NSMutableSet *submissions = [[NSMutableSet alloc] init];
    for (NSIndexPath *indexPath in [self.collectionView indexPathsForSelectedItems]) {
        SubmissionCell *cell = (SubmissionCell *)[self collectionView:self.collectionView cellForItemAtIndexPath:indexPath];
        if (cell.download.submission) {
            [submissions addObject:cell.download.submission];
        }
    }
    if([submissions count] > 0) {
        [self performSegueWithIdentifier:@"Publish Selected Files" sender:self];
    }
}

#pragma mark UICollectionViewDataSource methods
- (NSInteger)numberOfSectionsInCollectionView:(UICollectionView *)collectionView
{
    if(self.showFilter) {
        return [self.filteredProjects count];
    }
    return [self.projects count];
}

- (UICollectionReusableView *)collectionView:(UICollectionView *)collectionView viewForSupplementaryElementOfKind:(NSString *)kind atIndexPath:(NSIndexPath *)indexPath
{
    static NSString *headerIdentifier = @"ProjectHeaderIdentifier";
    static NSString *footerIdentifier = @"ProjectFooterIdentifier";
    ProjectHeaderView *headerView;
    ProjectFooterView *footerView;
    if ([kind isEqualToString:UICollectionElementKindSectionHeader]) {
        headerView = [self.collectionView dequeueReusableSupplementaryViewOfKind:UICollectionElementKindSectionHeader withReuseIdentifier:headerIdentifier forIndexPath:indexPath];
        Project *p = [self.filteredProjects objectAtIndex:[indexPath section]];
        NSString *headerName = [NSString stringWithFormat:@"%@ - %@ (%@-%@)",p.course.courseCode,p.projectName,p.course.year,p.course.semester];
        [headerView.sectionTitle setText:headerName];
        headerView.sectionFilter.tag = [indexPath section];
        if(p.isFiltered) {
            [headerView.sectionFilter setSelectedSegmentIndex:1];
        } else {
            [headerView.sectionFilter setSelectedSegmentIndex:0];
        }
    }
    if([kind isEqualToString:UICollectionElementKindSectionFooter]) {
        Project *p = [self.filteredProjects objectAtIndex:[indexPath section]];
        footerView = [self.collectionView dequeueReusableSupplementaryViewOfKind:UICollectionElementKindSectionFooter withReuseIdentifier:footerIdentifier forIndexPath:indexPath];
        if([p.submissions count] > 0 ) {
            footerView.numberItems.text = @"";
        } else {
            footerView.numberItems.text = @"No submissions";
        }
        return footerView;
    }

    return headerView;
}

- (NSInteger)collectionView:(UICollectionView *)collectionView numberOfItemsInSection:(NSInteger)section
{
    if(self.showFilter) {
        Project *project = [self.filteredProjects objectAtIndex:section];
        return [project.submissions count];
    }
    Project *project = [self.projects objectAtIndex:section];
    return [project.submissions count];
}

- (UICollectionViewCell *)collectionView:(UICollectionView *)collectionView cellForItemAtIndexPath:(NSIndexPath *)indexPath
{
    static NSString *cellIdentifier = @"SubmissionCellIdentifier";
    SubmissionCell *cell = [self.collectionView dequeueReusableCellWithReuseIdentifier:cellIdentifier forIndexPath:indexPath];
    
    // Configure the cell.
    Project *proj;
    if(self.showFilter) {
        proj = [self.filteredProjects objectAtIndex:indexPath.section];
    } else {
        proj = [self.projects objectAtIndex:indexPath.section];
    }
    SubmissionDownload *sub = [proj.submissions objectAtIndex:indexPath.item];
    cell.download = sub;
    cell.changedBox.hidden = YES;
    if(sub.submission.isPublishedValue) {
        cell.markedBox.hidden = NO;
        /*if(sub.submission.hasLocalChangesValue) {
            cell.changedBox.hidden = NO;
            cell.markedBox.hidden = YES;
        }*/
    } else {
        cell.markedBox.hidden = YES;
        if(sub.submission.hasLocalChangesValue) {
            cell.changedBox.hidden = NO;
        }
    }
    
    // Kludge to work around API sometimes returning NSNull* for titles.
    if ([sub.title isEqual:[NSNull null]]) {
        cell.documentTitleLabel.text = @"";
    } else {
        cell.documentTitleLabel.text = sub.title;
    }

    
    [self refreshCell:cell forceUpdate:YES];
    
    cell.documentStudentNoLabel.text = sub.uqId;
    
    return cell;
}

- (void)refreshCell:(SubmissionCell *)cell forceUpdate:(BOOL)force {
    UIImage __block *frontPage = [self.frontpageThumbs objectForKey:@(cell.download.submissionId)];
    if (!frontPage || force) {
        dispatch_async(dispatch_get_main_queue(), ^{
            //DLog(@"UPDATING FOR REAL %d",cell.download.submissionId);
            frontPage = [UIImage imageWithContentsOfFile:[cell.download.submission thumbnailPath]];
            if (frontPage) {
                //DLog(@"FOUND ONE");
                [self.frontpageThumbs setObject:frontPage forKey:@(cell.download.submissionId)];
                cell.documentThumb.image = frontPage;
            } else {
                cell.documentThumb.image = [UIImage imageNamed:@"defaultsubmission.png"];
            }
        });
    }
}

- (void) updatePublishButtonText {
    if([[self.collectionView indexPathsForSelectedItems] count] == 0) {
        [self.editButtonItem setTintColor:UIColorFromRGB(0x915626)];
        [self.editButtonItem setEnabled:NO];
    } else {
        [self.editButtonItem setTintColor:UIColorFromRGB(0x0000FF)];
        [self.editButtonItem setEnabled:YES];
    }
    [self.editButtonItem setTitle:[NSString stringWithFormat:@"Upload (%lu)",[[self.collectionView indexPathsForSelectedItems] count]]];
}

#pragma mark UICollectionViewDelegate methods

- (void)collectionView:(UICollectionView *)collectionView didDeselectItemAtIndexPath:(NSIndexPath *)indexPath {
    if (_inPublishMode) {
        [self updatePublishButtonText];
        return;
    }
}

- (void)collectionView:(UICollectionView *)collectionView didSelectItemAtIndexPath:(NSIndexPath *)indexPath
{
    SubmissionCell *cell = (SubmissionCell *)[self.collectionView cellForItemAtIndexPath:indexPath];
    
    if (_inPublishMode) {
        [self updatePublishButtonText];
        return;
    }
    [self.collectionView deselectItemAtIndexPath:indexPath animated:YES];
    if(self.isRefreshing || self.isTransitioning) {
        return;
    }
    
    Project *proj;
    if(self.showFilter) {
        proj = [self.filteredProjects objectAtIndex:indexPath.section];
    } else {
        proj = [self.projects objectAtIndex:indexPath.section];
    }
    SubmissionDownload *subdl = [proj.submissions objectAtIndex:indexPath.item];
    
    Submission *sub = cell.download.submission;
    if (sub) {
        NSLog(@"OPENING");
        [self performSegueWithIdentifier:@"Open Submission" sender:cell];
        self.isTransitioning = YES;
    } else {
        self.isTransitioning = YES;
        cell.downloadProgress.hidden = NO;
        cell.downloadProgress.progress = 0.0;
        [[MarkupAPIController sharedApi] downloadSubmissionFileWithId:subdl.submissionId withSuccess:^(NSString *tempFilePath) {
            [self saveSubmissionForCellDownload:subdl withTempFile:tempFilePath];
            [self saveCoverImageForSubmission:subdl.submission];
            [self performSegueWithIdentifier:@"Open Submission" sender:cell];
            NSLog(@"OPENING");
            [cell.downloadProgress setHidden:YES];
        } failure:^(NSError *error) {
            [cell.downloadProgress setHidden:YES];
            
            UIAlertView *downloadFailed = [[UIAlertView alloc] initWithTitle:@"Couldn't get submission" message:@"We couldn't download the submission document. Please try again later." delegate:self cancelButtonTitle:@"OK" otherButtonTitles:nil];
            [downloadFailed show];
            self.isTransitioning = NO;
        } andProgress:^(float percentComplete) {
            cell.downloadProgress.progress = percentComplete;
        }];
    }
}

- (void)saveSubmissionForCellDownload:(SubmissionDownload *)submissionDownload withTempFile:(NSString *)tempFilePath
{
    Submission *sub = [Submission createEntity];
    sub.courseUid = submissionDownload.courseId;
    NSString *filename = [[submissionDownload.title stringByReplacingOccurrencesOfString:@" " withString:@""] stringByDeletingPathExtension];
    sub.localDirectoryName = filename;
    sub.submissionId = @(submissionDownload.submissionId);
    sub.projectId = submissionDownload.project.projectId;
    NSError *error;
    if (![[NSFileManager defaultManager] fileExistsAtPath:[sub localPath]]) {
        [[NSFileManager defaultManager] createDirectoryAtPath:[sub localPath] withIntermediateDirectories:YES attributes:nil error:nil];
        [[NSFileManager defaultManager] createDirectoryAtPath:[sub localAnnotationDirectory] withIntermediateDirectories:YES attributes:nil error:nil];
    }
    [[NSFileManager defaultManager] copyItemAtPath:tempFilePath toPath:[sub localPDFPath] error:&error];
    
    [[NSManagedObjectContext defaultContext] save];
    
    submissionDownload.submission = sub;
}

- (void)saveCoverImageForSubmission:(Submission *)sub
{
    NSData *pdfData = [NSData dataWithContentsOfFile:sub.localPDFPath];
    CGDataProviderRef pdfProvider = CGDataProviderCreateWithCFData((__bridge CFDataRef)(pdfData));
    CGPDFDocumentRef pdf = CGPDFDocumentCreateWithProvider(pdfProvider);
    CGPDFPageRef frontPage = CGPDFDocumentGetPage(pdf, 1);
    
    CGSize cellSize = kSubmissionInCellSize;
    
    UIGraphicsBeginImageContextWithOptions(kSubmissionInCellSize, YES, 0.0);
    CGContextRef ctx = UIGraphicsGetCurrentContext();
    
    // Capture a thumbnail of the front page of the document
    CGRect pageRect = CGPDFPageGetBoxRect(frontPage, kCGPDFMediaBox);
    float pdfScale =  cellSize.height / pageRect.size.height;
    pageRect.size = CGSizeMake(pageRect.size.width * pdfScale, pageRect.size.height * pdfScale);
    pageRect.origin = CGPointZero;
    CGContextSaveGState(ctx);
    CGContextSetRGBFillColor(ctx, 1.0, 1.0, 1.0, 1.0);
    CGContextFillRect(ctx, CGContextGetClipBoundingBox(ctx));
    CGContextTranslateCTM(ctx, 0.0, pageRect.size.height);
    CGContextScaleCTM(ctx, 1.0, -1.0);
    CGContextScaleCTM(ctx, pdfScale, pdfScale);
    
    /* SPEED UP MEMORY PROCESSING */
    CGContextSetInterpolationQuality(ctx, kCGInterpolationHigh);
    CGContextSetRenderingIntent(ctx, kCGRenderingIntentDefault);
    /* END SPEED */

    CGContextDrawPDFPage(ctx, frontPage);
    CGContextRestoreGState(ctx);
    
    DLog(@"UPDATING COVER");
    NSSet *annots = sub.annotations;
    for(Annotation *ann in annots) {
        if(([ann.annotationType isEqualToString:@"Highlight"] || [ann.annotationType isEqualToString:@"Freehand"]) && [ann.pageNumber isEqual: @1]) {
            CGRect annotRect = CGRectMake(
                                          [ann.xPos floatValue]*pageRect.size.width,
                                          [ann.yPos floatValue]*pageRect.size.height,
                                          [ann.width floatValue]*pageRect.size.width,
                                          [ann.height floatValue]*pageRect.size.height);
            [[UIImage imageWithContentsOfFile:ann.localFilePath] drawInRect:annotRect];
        }
        else if([ann.annotationType isEqualToString:@"Text"] && [ann.pageNumber isEqual: @1]) {
            CGRect annotRect = CGRectMake(
                                          ([ann.xPos floatValue]*pageRect.size.width)+4*pdfScale,
                                          ([ann.yPos floatValue]*pageRect.size.height)+5*pdfScale,
                                          [ann.width floatValue]*pageRect.size.width,
                                          [ann.height floatValue]*pageRect.size.height);
            UIColor *textColour = [UIColor colorWithHexString:ann.colour];
            UIFont *font = [UIFont systemFontOfSize:9.0*pdfScale];
            CGContextSetFillColorWithColor(ctx, textColour.CGColor);
            NSDictionary *dict = @{ NSFontAttributeName: font, NSForegroundColorAttributeName: textColour};
            [ann.title drawInRect:annotRect withAttributes:dict];
        }
        else if([ann.annotationType isEqualToString:@"Recording"] && [ann.pageNumber isEqual: @1]) {
            CGRect annotRect = CGRectMake(
                                          ([ann.xPos floatValue]*pageRect.size.width)+9*pdfScale,
                                          ([ann.yPos floatValue]*pageRect.size.height)+12*pdfScale,
                                          30*pdfScale,
                                          30*pdfScale);
            UIImage *audioBadge = [UIImage imageNamed:@"audio-icon_onpdf"];
            [audioBadge drawInRect:annotRect];
        }
    }
    
    UIImage *frontPageImage = UIGraphicsGetImageFromCurrentImageContext();
    
    UIGraphicsEndImageContext();
    CGPDFDocumentRelease(pdf);
    CGDataProviderRelease(pdfProvider);
    
    NSString *thumbnailPath = [sub thumbnailPath];
    for(SubmissionCell *cell in [self.collectionView visibleCells]) {
        if([sub.submissionId intValue] == cell.download.submissionId) {
            [self refreshCell:cell forceUpdate:YES];
        }
    }
    //[self.collectionView reloadData];
    [UIImagePNGRepresentation(frontPageImage) writeToFile:thumbnailPath atomically:YES];
}

- (void)keyboardWillShow:(NSNotification *)not
{
    NSLog(@"KEYBOARD WILL SHOW");
    NSDictionary *userInfo = [not userInfo];
    NSTimeInterval animationDuration;
    UIViewAnimationCurve animationCurve;
    CGRect keyboardFrame;
    
    [[userInfo objectForKey:UIKeyboardAnimationDurationUserInfoKey] getValue:&animationDuration];
    [[userInfo objectForKey:UIKeyboardAnimationCurveUserInfoKey] getValue:&animationCurve];
    [[userInfo objectForKey:UIKeyboardFrameEndUserInfoKey] getValue:&keyboardFrame];
    keyboardFrame = [self.view.window convertRect:keyboardFrame toView:self.view];
    
    [UIView beginAnimations:nil context:nil];
    [UIView setAnimationDuration:animationDuration];
    [UIView setAnimationCurve:animationCurve];
    [self.collectionView setFrame:CGRectMake(self.collectionView.frame.origin.x, self.collectionView.frame.origin.y, self.collectionView.frame.size.width, self.collectionView.frame.size.height-keyboardFrame.size.height)];
    UIToolbar *toolbar = self.navigationController.toolbar;
    [self.navigationController.toolbar setFrame:CGRectMake(toolbar.frame.origin.x, toolbar.frame.origin.y - keyboardFrame.size.height, toolbar.frame.size.width, toolbar.frame.size.height)];
    [UIView commitAnimations];
    [self.navigationController setToolbarHidden:NO];
    [self.collectionView addGestureRecognizer:self.tapToRemoveKeyboardGesture];
}

-(BOOL) shouldInvalidateLayoutForBoundsChange:(CGRect)newBounds {
    return YES;
}


- (void)keyboardWillHide:(NSNotification *)not
{
    NSLog(@"KEYBOARD WILL HIDE");
    NSDictionary *userInfo = [not userInfo];
    NSTimeInterval animationDuration;
    UIViewAnimationCurve animationCurve;
    CGRect keyboardFrame;
    
    [[userInfo objectForKey:UIKeyboardAnimationDurationUserInfoKey] getValue:&animationDuration];
    [[userInfo objectForKey:UIKeyboardAnimationCurveUserInfoKey] getValue:&animationCurve];
    [[userInfo objectForKey:UIKeyboardFrameBeginUserInfoKey] getValue:&keyboardFrame];
    keyboardFrame = [self.view.window convertRect:keyboardFrame toView:self.view];
    
    [UIView beginAnimations:nil context:nil];
    [UIView setAnimationDuration:animationDuration];
    [UIView setAnimationCurve:animationCurve];

    [self.collectionView setFrame:CGRectMake(self.collectionView.frame.origin.x, self.collectionView.frame.origin.y, self.collectionView.frame.size.width, self.collectionView.frame.size.height+keyboardFrame.size.height)];
    UIToolbar *toolbar = self.navigationController.toolbar;
    [self.navigationController.toolbar setFrame:CGRectMake(toolbar.frame.origin.x, toolbar.frame.origin.y + keyboardFrame.size.height, toolbar.frame.size.width, toolbar.frame.size.height)];
    [UIView commitAnimations];
    [self.collectionView removeGestureRecognizer:self.tapToRemoveKeyboardGesture];
}

- (void)searchBarSearchButtonClicked:(UISearchBar *)searchBar {
    [searchBar resignFirstResponder];
}

- (void)searchBar:(UISearchBar *)searchBar textDidChange:(NSString *)searchText {
    self.searchText = searchText;
    [self updateFilter];
}

- (IBAction)changeGlobalFilterType:(id)sender {
    DLog(@"CHANGING FILTER TYPE");
    [self updateFilter];
}

- (IBAction)sectionFilterClicked:(UISegmentedControl *)sender {
    DLog(@"%d",[sender tag]);
    if([sender selectedSegmentIndex] == 0) {
        [[self.projects objectAtIndex:[sender tag]] setIsFiltered:NO];
        DLog(@"NO FILTER");
    } else {
        [[self.projects objectAtIndex:[sender tag]] setIsFiltered:YES];
        DLog(@"YES FILTER");
    }
    [self updateFilter];
}

- (void)updateFilter {
    NSString *searchTerms = self.searchText;
    if([searchTerms isEqualToString:@""]) {
        searchTerms = @".";
    }
    NSPredicate *predicate = [NSPredicate predicateWithFormat:@"(uqId CONTAINS[cd] %@) OR (title CONTAINS[cd] %@)",searchTerms,searchTerms];
    NSPredicate *filteredPredicate = [NSPredicate predicateWithFormat:@"((uqId CONTAINS[cd] %@) OR (title CONTAINS[cd] %@)) AND (submission.hasLocalChanges == NO OR submission = nil)",searchTerms,searchTerms];
    self.filteredProjects = [[NSMutableArray alloc] initWithArray:self.projects copyItems:YES];
    for(Project *filteredProject in self.filteredProjects) {
        NSArray *filteredSubmissions;
        if([[self mainFilter] selectedSegmentIndex] == 1 || filteredProject.isFiltered) {
            filteredSubmissions = [filteredProject.submissions filteredArrayUsingPredicate:filteredPredicate];
        } else {
            filteredSubmissions = [filteredProject.submissions filteredArrayUsingPredicate:predicate];
        }
        filteredProject.submissions = filteredSubmissions;//[filteredSubmissions copy];
        for(SubmissionDownload *sub in filteredProject.submissions) {
            if(sub.submission.hasLocalChanges) {
                NSLog(@"sub has changes");
            } else {
                NSLog(@"sub has no changes");
            }
        }
    }
    [self.collectionView reloadData];
}



#pragma mark Login Delegate methods
- (void)didLogin
{
    NSLog(@"did login -- added keyboard observer");
    [[NSNotificationCenter defaultCenter] addObserver:self selector:@selector(keyboardWillShow:) name:UIKeyboardWillShowNotification object:nil];
    [[NSNotificationCenter defaultCenter] addObserver:self selector:@selector(keyboardWillHide:) name:UIKeyboardWillHideNotification object:nil];
    self.logoutButton.enabled = YES;
    [self refreshProjects:self];
}

- (IBAction)changeFilterType:(id)sender {
    
}

#pragma mark PublishViewControllerDelegate methods
- (void)publishViewFailedToPublish
{
    [self dismissViewControllerAnimated:YES completion:^{
        UIAlertView *failedToPublish = [[UIAlertView alloc] initWithTitle:@"Failed to publish" message:@"We couldn't publish your submissions. Is internet availalbe? Can you get to Google?" delegate:self cancelButtonTitle:@"Ok" otherButtonTitles:nil];
        [failedToPublish show];
    }];
}

- (void)publishViewWillDismiss
{
    [self dismissViewControllerAnimated:YES completion:^{
        [self togglePublishMode:self];
        for (NSIndexPath *index in [self.collectionView indexPathsForSelectedItems]) {
            [self.collectionView deselectItemAtIndexPath:index animated:NO];
        }
        [self.collectionView reloadData];
    }];
}

- (IBAction)logout:(id)sender {
    [[MarkupAPIController sharedApi] logoutWithSucess:^{
        NSLog(@"did logout -- removed keyboard observer");
        [[NSNotificationCenter defaultCenter]  removeObserver:self name:UIKeyboardWillShowNotification object:nil];
        [[NSNotificationCenter defaultCenter]  removeObserver:self name:UIKeyboardWillHideNotification object:nil];

        [self performSegueWithIdentifier:@"Show Login" sender:self];
        self.logoutButton.enabled = NO;
    } andFailure:^(NSError *error) {
        NSLog(@"Couldn't log out. Why? %@", error);
    }];
}

- (IBAction)downloadAll:(id)sender {
    AppDelegate *appDelegate = (AppDelegate*)[[UIApplication sharedApplication] delegate];
    if (appDelegate.reach.isReachable) {
        NSLog(@"DOWNLOADING EVERYTHING");
        for(Project *section in self.projects) {
            for(SubmissionDownload *sub in section.submissions) {
                if (!sub.submission) {
                    SubmissionCell *selectedCell = nil;
                    for(SubmissionCell *cell in [self.collectionView visibleCells]) {
                        if([[cell download] submissionId] == [sub submissionId]) {
                            selectedCell = cell;
                            break;
                        }
                    }
                    if(selectedCell) {
                        selectedCell.downloadProgress.hidden = NO;
                        selectedCell.downloadProgress.progress = 0.0;
                    }
                    [[MarkupAPIController sharedApi] downloadSubmissionFileWithId:[sub submissionId]    withSuccess:^(NSString *tempFilePath) {
                        if(selectedCell) {
                            [selectedCell.downloadProgress setHidden:YES];
                        }
                        [self saveSubmissionForCellDownload:sub withTempFile:tempFilePath];
                        [self saveCoverImageForSubmission:sub.submission];
                    } failure:^(NSError *error) {
                        if(selectedCell) {
                            [selectedCell.downloadProgress setHidden:YES];
                        }
                    } andProgress:^(float percentComplete) {
                        if(selectedCell) {
                            selectedCell.downloadProgress.progress = percentComplete;
                        }
                    }];
                }
            }
        }
    } else {
        UIAlertView *noProjects = [[UIAlertView alloc] initWithTitle:@"No Connection" message:@"You must be connected to the internet to download all projects." delegate:self cancelButtonTitle:@"Ok" otherButtonTitles: nil];
        [noProjects show];
    }
}

@end
