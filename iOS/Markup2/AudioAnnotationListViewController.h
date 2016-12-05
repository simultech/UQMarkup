//
//  AudioAnnotationListViewController.h
//  Markup2
//

#import <UIKit/UIKit.h>
#import "Drawer.h"
#import "AudioPlayer.h"
#import "SequentialPDFViewController.h"

@interface AudioAnnotationListViewController : UITableViewController <Drawer>

@property (weak) IBOutlet AudioPlayer *player;
@property (nonatomic, weak) SequentialPDFViewController *pdfViewController;

@end
