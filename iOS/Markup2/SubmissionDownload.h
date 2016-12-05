//
//  SubmissionDownload.h
//  Markup2
//

#import <Foundation/Foundation.h>
#import "Submission.h"
#import "Project.h"

@interface SubmissionDownload : NSObject <NSCoding>

@property (nonatomic, strong) NSString *courseId;
@property (nonatomic, assign) NSInteger submissionId;
@property (nonatomic, strong) NSString *uqId;
@property (nonatomic, strong) NSString *title;
@property (nonatomic, strong) Submission *submission;
@property (nonatomic, weak) Project *project;

@end
