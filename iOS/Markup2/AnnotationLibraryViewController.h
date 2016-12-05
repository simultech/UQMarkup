//
//  AnnotationLibraryViewController.h
//  Markup2
//

#import <UIKit/UIKit.h>
#import "SequentialPDFViewController.h"
#import "Drawer.h"

@interface AnnotationLibraryViewController : UITableViewController <UITableViewDataSource, UITableViewDelegate, Drawer>
@property (nonatomic, weak) SequentialPDFViewController *pdfViewController;
@end
