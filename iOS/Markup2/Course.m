//
//  Course.m
//  Markup2
//

#import "Course.h"

@implementation Course

- (id)initWithCoder:(NSCoder *)aDecoder
{
    self = [super init];
    if (self) {
        self.courseId = [aDecoder decodeObjectForKey:@"courseId"];
        self.courseCode = [aDecoder decodeObjectForKey:@"courseCode"];
        self.shadowCode = [aDecoder decodeObjectForKey:@"shadowCode"];
        self.courseName = [aDecoder decodeObjectForKey:@"courseName"];
        self.year = [aDecoder decodeObjectForKey:@"year"];
        self.semester = [aDecoder decodeObjectForKey:@"semester"];
    }
    return self;
}

- (void)encodeWithCoder:(NSCoder *)aCoder
{
    [aCoder encodeObject:self.courseId forKey:@"courseId"];
    [aCoder encodeObject:self.courseCode forKey:@"courseCode"];
    [aCoder encodeObject:self.shadowCode forKey:@"shadowCode"];
    [aCoder encodeObject:self.courseName forKey:@"courseName"];
    [aCoder encodeObject:self.year forKey:@"year"];
    [aCoder encodeObject:self.semester forKey:@"semester"];
}

@end
