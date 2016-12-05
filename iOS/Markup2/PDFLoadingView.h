//
//  PDFLoadingView.h
//  Markup2
//

#import <UIKit/UIKit.h>
#import <QuartzCore/QuartzCore.h>

@interface PDFLoadingView : UIView

@property (nonatomic,strong) UIActivityIndicatorView *spinner;
@property (nonatomic,strong) UILabel *loadingText;

- (void) fadeOut;

@end
