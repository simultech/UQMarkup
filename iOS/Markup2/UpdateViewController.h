//
//  UpdateViewController.h
//  Markup2
//

#import <UIKit/UIKit.h>

@protocol UpdateDelegate;
@interface UpdateViewController : UIViewController

@property (nonatomic, weak) id<UpdateDelegate> delegate;
- (IBAction)closeClicked:(id)sender;
@property (weak, nonatomic) IBOutlet UILabel *versionText;
@property (weak, nonatomic) IBOutlet UITextView *releaseNotes;
- (IBAction)openAppStoreToUpdate:(id)sender;

@end

@protocol UpdateDelegate
@property (nonatomic,strong) NSDictionary *versionInfo;

@end
