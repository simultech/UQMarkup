//
//  Project.m
//  Markup2
//

#import "Project.h"
#import "SubmissionDownload.h"
#import "Rubric.h"

@implementation Project

- (id)initWithCoder:(NSCoder *)aDecoder
{
    self = [super init];
    if (self) {
        self.projectId = [aDecoder decodeObjectForKey:@"projectId"];
        self.projectName = [aDecoder decodeObjectForKey:@"projectName"];
        self.projectDescription = [aDecoder decodeObjectForKey:@"projectDescription"];
        self.startDate = [aDecoder decodeObjectForKey:@"startDate"];
        self.endDate = [aDecoder decodeObjectForKey:@"endDate"];
        self.submissionDate = [aDecoder decodeObjectForKey:@"submissionDate"];
        self.submissions = [aDecoder decodeObjectForKey:@"submissions"];
        self.rubrics = [aDecoder decodeObjectForKey:@"rubrics"];
        self.course = [aDecoder decodeObjectForKey:@"course"];
    }
    
    for (SubmissionDownload *sub in self.submissions) {
        sub.project = self;
    }
    
    return self;
}

- (void)encodeWithCoder:(NSCoder *)aCoder
{
    [aCoder encodeObject:self.projectId forKey:@"projectId"];
    [aCoder encodeObject:self.projectName forKey:@"projectName"];
    [aCoder encodeObject:self.projectDescription forKey:@"projectDescription"];
    [aCoder encodeObject:self.startDate forKey:@"startDate"];
    [aCoder encodeObject:self.endDate forKey:@"endDate"];
    [aCoder encodeObject:self.submissionDate forKey:@"submissionDate"];
    [aCoder encodeObject:self.submissions forKey:@"submissions"];
    [aCoder encodeObject:self.rubrics forKey:@"rubrics"];
    [aCoder encodeObject:self.course forKey:@"course"];
}

-(id)copyWithZone:(NSZone *)zone
{
    // We'll ignore the zone for now
    Project *another = [[Project alloc] init];
    another.projectId = self.projectId;
    another.projectName = self.projectName;
    another.projectDescription = self.projectDescription;
    another.startDate = self.startDate;
    another.endDate = self.endDate;
    another.submissions = [self.submissions copyWithZone:zone];
    another.rubrics = [self.rubrics copyWithZone:zone];
    another.course = self.course;
    another.isFiltered = self.isFiltered;
    return another;
}

- (NSString *)description
{
    return [NSString stringWithFormat:@"Project %@:\nSubmissions:\n%@", self.projectName, self.submissions];
}

@end
