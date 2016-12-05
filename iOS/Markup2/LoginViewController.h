//
//  LoginViewController.h
//  Markup2
//

#import <UIKit/UIKit.h>

@protocol LoginDelegate;
@interface LoginViewController : UIViewController <UITextFieldDelegate,UIPickerViewDataSource,UIPickerViewDelegate>
@property (nonatomic, weak) id<LoginDelegate> delegate;
@property (strong) NSArray *institutions;

@end

@protocol LoginDelegate
- (void)didLogin;

@end
