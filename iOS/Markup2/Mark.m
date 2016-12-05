#import "Mark.h"

@implementation Mark

// Custom logic goes here.

- (NSString *)description
{
    return [NSString stringWithFormat:@"Mark: %@ for Rubric Id: %@", self.value, self.rubricId];
}

- (NSDictionary *)toDict
{
    return @{
        @"project_id": self.projectId,
        @"rubric_id": self.rubricId,
        @"value": self.value
    };
}

@end
