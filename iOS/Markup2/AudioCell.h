//
//  AudioCell.h
//  Markup2
//

#import <UIKit/UIKit.h>

@interface AudioCell : UITableViewCell

@property (nonatomic,strong) IBOutlet UILabel *annotationName;
@property (nonatomic,strong) IBOutlet UILabel *annotationDuration;
@property (nonatomic,strong) IBOutlet UILabel *annotationPage;
@property (nonatomic,strong) UITextField *annotationEdit;

@end
