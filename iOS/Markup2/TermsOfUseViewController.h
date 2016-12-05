//
//  TermsOfUseViewController.h
//  Markup2
//

#import <UIKit/UIKit.h>
#import "LoginViewController.h"

@interface TermsOfUseViewController : UIViewController

@property (nonatomic,weak) IBOutlet UIWebView *webView;
@property (nonatomic, weak) id<LoginDelegate> delegate;

@end
