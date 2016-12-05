//
//  ProjectCollectionsViewController.h
//  SectionCollectionViewTest
//

#import <UIKit/UIKit.h>
#import "SequentialPDFViewController.h"

#define kSubmissionInCellSize CGSizeMake(196.0, 276.0)

@interface ProjectCollectionsViewController : UICollectionViewController <UICollectionViewDataSource, UICollectionViewDelegate, UISearchDisplayDelegate, UISearchBarDelegate, UpdateFrontPageProtocol, UIGestureRecognizerDelegate, UIAlertViewDelegate>

@property (nonatomic,assign) BOOL showFilter;
@property (nonatomic,assign) BOOL isRefreshing;
@property (nonatomic,assign) BOOL showingDeleteConfirm;
@property (nonatomic,assign) BOOL isTransitioning;

@property (nonatomic,strong) NSString *searchText;
@property (nonatomic,strong) NSIndexPath *toDeleteIndexPath;
@property (weak, nonatomic) IBOutlet UISegmentedControl *mainFilter;
@property (nonatomic,strong) UITapGestureRecognizer *tapToRemoveKeyboardGesture;

@property (nonatomic,strong) NSDictionary *versionInfo;

- (IBAction)changeGlobalFilterType:(id)sender;
- (IBAction)sectionFilterClicked:(UISegmentedControl *)sender;

@end
