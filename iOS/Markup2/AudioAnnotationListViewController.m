//
//  AudioAnnotationListViewController.m
//  Markup2
//

#import "AudioAnnotationListViewController.h"
#import "AudioAnnotationManager.h"

@interface AudioAnnotationListViewController ()

@end

@implementation AudioAnnotationListViewController

- (void)viewDidLoad
{
    [super viewDidLoad];

    // Uncomment the following line to preserve selection between presentations.
    // self.clearsSelectionOnViewWillAppear = NO;
 
    // Uncomment the following line to display an Edit button in the navigation bar for this view controller.
    // self.navigationItem.rightBarButtonItem = self.editButtonItem;
}

- (void)viewWillAppear:(BOOL)animated
{
    [super viewWillAppear:animated];
    [self.tableView reloadData];
    [self.tableView setBackgroundColor:[UIColor whiteColor]];
    [self.tableView setAllowsSelectionDuringEditing:YES];
}

- (void)didReceiveMemoryWarning
{
    [super didReceiveMemoryWarning];
    // Dispose of any resources that can be recreated.
}

#pragma mark Drawer protocol methods
- (void)drawerWillHide
{
    
}

- (void)drawerWillShow
{
    [self.tableView reloadData];
}

- (void)dealloc {
    self.player = nil;
}

@end
