//
//  Rubric.m
//  Markup2
//

#import "Rubric.h"

@implementation Rubric

- (id)initWithCoder:(NSCoder *)aDecoder
{
    self = [super init];
    if (self) {
        self.projectId = [aDecoder decodeIntegerForKey:@"projectId"];
        self.rubricId = [aDecoder decodeIntegerForKey:@"rubricId"];
        self.rubricName = [aDecoder decodeObjectForKey:@"rubricName"];
        self.rubricType = [aDecoder decodeIntForKey:@"rubricType"];
        self.rubricSection = [aDecoder decodeObjectForKey:@"rubricSection"];
        self.rubricValue = [aDecoder decodeObjectForKey:@"rubricValue"];
        self.rubricMeta = [aDecoder decodeObjectForKey:@"rubricMeta"];
    }
    return self;
}

- (void)encodeWithCoder:(NSCoder *)aCoder
{
    [aCoder encodeInteger:self.projectId forKey:@"projectId"];
    [aCoder encodeInteger:self.rubricId forKey:@"rubricId"];
    [aCoder encodeObject:self.rubricName forKey:@"rubricName"];
    [aCoder encodeInt:self.rubricType forKey:@"rubricType"];
    [aCoder encodeObject:self.rubricSection forKey:@"rubricSection"];
    [aCoder encodeObject:self.rubricValue forKey:@"rubricValue"];
    [aCoder encodeObject:self.rubricMeta forKey:@"rubricMeta"];
}

- (NSString *)description
{
    return [NSString stringWithFormat:@"Rubric %d, %@. Value: %@", self.rubricId, self.rubricName, self.rubricValue];
}

#pragma mark - NSCopying methods
- (id)copyWithZone:(NSZone *)zone
{
    Rubric *copy = [[[self class] allocWithZone:zone] init];
    copy.projectId = self.projectId;
    copy.rubricId = self.rubricId;
    copy.rubricName = [self.rubricName copy];
    copy.rubricType = self.rubricType;
    copy.rubricSection = [self.rubricSection copy];
    copy.rubricValue = [self.rubricValue copy];
copy.rubricMeta = [self.rubricMeta copy];

    return copy;
}

@end
