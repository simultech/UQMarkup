//
//  ProjectHeaderView.h
//  Markup2
//

#import <UIKit/UIKit.h>

@interface ProjectHeaderView : UICollectionReusableView

@property (weak, nonatomic) IBOutlet UILabel *sectionTitle;
@property (weak, nonatomic) IBOutlet UISegmentedControl *sectionFilter;
- (IBAction)sectionChanged:(id)sender;

@end
