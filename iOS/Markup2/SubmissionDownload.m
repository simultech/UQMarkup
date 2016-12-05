//
//  SubmissionDownload.m
//  Markup2
//

#import "SubmissionDownload.h"
#import "Submission.h"

@implementation SubmissionDownload

- (id)initWithCoder:(NSCoder *)aDecoder
{
    self = [super init];
    if (self) {
        self.courseId = [aDecoder decodeObjectForKey:@"courseId"];
        self.submissionId = [aDecoder decodeIntForKey:@"submissionId"];
        self.uqId = [aDecoder decodeObjectForKey:@"uqId"];
        self.title = [aDecoder decodeObjectForKey:@"title"];
        
        self.submission = [Submission findFirstByAttribute:@"submissionId" withValue:@(self.submissionId)];
    }
    return self;
}

- (void)encodeWithCoder:(NSCoder *)aCoder
{
    [aCoder encodeObject:self.courseId forKey:@"courseId"];
    [aCoder encodeInt:self.submissionId forKey:@"submissionId"];
    [aCoder encodeObject:self.uqId forKey:@"uqId"];
    [aCoder encodeObject:self.title forKey:@"title"];
}

- (NSString *)description
{
    return [NSString stringWithFormat:@"Submission: %d\n for %@", self.submissionId, self.uqId];
}

@end
