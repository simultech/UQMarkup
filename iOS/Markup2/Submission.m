#import "Submission.h"
#import "Annotation.h"

@implementation Submission

// Custom logic goes here.
- (NSString *)localPath
{
    NSArray *paths = NSSearchPathForDirectoriesInDomains(NSDocumentDirectory, NSUserDomainMask, YES);
    if (paths.count > 0) {
        NSString *_localPath = [[[[paths objectAtIndex:0] stringByAppendingPathComponent:@"assignments"] stringByAppendingPathComponent:self.projectId] stringByAppendingPathComponent:self.localDirectoryName];
        if (![[NSFileManager defaultManager] fileExistsAtPath:_localPath]) {
            [[NSFileManager defaultManager] createDirectoryAtPath:_localPath withIntermediateDirectories:YES attributes:nil error:nil];
        }
        return _localPath;
    }

    return nil;
}

- (NSString *)localPDFPath
{
    return [[self localPath] stringByAppendingPathComponent:[NSString stringWithFormat:@"%@.pdf", self.localDirectoryName]];
}

- (NSString *)localBakedPDFPath
{
    return [[self localPath] stringByAppendingPathComponent:[NSString stringWithFormat:@"%@_baked.pdf", self.localDirectoryName]];
}

- (NSString *)localAnnotationDirectory
{
    
    NSString *annotDirectory = [[self localPath] stringByAppendingPathComponent:@"annots"];
    NSFileManager *fileMan = [NSFileManager defaultManager];
    if (![fileMan fileExistsAtPath:annotDirectory]) {
        [fileMan createDirectoryAtPath:annotDirectory withIntermediateDirectories:YES attributes:nil error:nil];
    }
    return annotDirectory;
}

- (NSString *)thumbnailPath
{
    return [[self localPath] stringByAppendingPathComponent:[NSString stringWithFormat:@"%@.png", self.localDirectoryName]];
}

- (NSDictionary *)toDict
{
    NSMutableArray *annots = [[NSMutableArray alloc] initWithCapacity:self.annotations.count];
    for (Annotation *annot in self.annotations) {
        [annots addObject:[annot toDict]];
    }
    
    
    
    return @{
        @"id": self.submissionId,
        @"course_uid": self.courseUid,
        @"project_id": self.projectId,
        @"annots": annots
    };
}

@end
