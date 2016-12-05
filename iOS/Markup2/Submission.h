#import "_Submission.h"

@interface Submission : _Submission {}
// Custom logic goes here.
- (NSString *)localPath;
- (NSString *)localPDFPath;
- (NSString *)localBakedPDFPath;
- (NSString *)localAnnotationDirectory;
- (NSString *)thumbnailPath;

@end
