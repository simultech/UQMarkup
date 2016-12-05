//
//  SequentialPDFViewController.h
//  Markup2
//

#import <UIKit/UIKit.h>
#import "MainToolbar.h"
#import "PDFLoadingView.h"

@class Submission;


@protocol UpdateFrontPageProtocol;
@class PDFScrollView;
@interface SequentialPDFViewController : UIViewController
@property (nonatomic, strong) Submission *submission;
@property (nonatomic, strong) PDFScrollView *scrollView;
@property (weak) id<UpdateFrontPageProtocol> delegate;
@property (weak, nonatomic) IBOutlet MainToolbar *mainToolbar;

@property (nonatomic, strong) PDFLoadingView *loadingView;
@property (nonatomic, strong) NSArray *rubData;
@property (nonatomic, strong) NSString *subTitle;

- (void)setRubricData:(NSArray *)rubricData;
- (void)softClearAnnotations;
- (void)insertAnnotations;
- (void)setSubmissionTitle:(NSString *)submissionTitle;
- (void)showRecordingImage;
- (void)hideRecordingImage;
- (void) forceStopRecordingFromClick;
- (BOOL) saveLogWithType:(NSString *)type andAction:(NSString *)action andValue:(NSString *)value;
@end

@protocol UpdateFrontPageProtocol <NSObject>
- (void)saveCoverImageForSubmission:(Submission *)sub;

@end
