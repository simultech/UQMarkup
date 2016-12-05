//
//  PublishViewController.h
//  Markup2
//

#import <UIKit/UIKit.h>

@protocol PublishViewControllerDelegate;
@interface PublishViewController : UIViewController
@property (nonatomic, strong) NSArray *submissions;
@property (nonatomic, weak) id<PublishViewControllerDelegate> delegate;

- (void)uploadEditedSubmissions;
@end

@protocol PublishViewControllerDelegate

- (void)publishViewWillDismiss;
- (void)publishViewFailedToPublish;
@end
