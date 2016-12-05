#import "Log.h"


@interface Log ()

// Private interface goes here.

@end


@implementation Log

- (NSString *)description
{
    return [NSString stringWithFormat:@"Action: %@ Type: %@ Value: %@ Created: %@", self.action, self.type, self.value, self.created];
}

- (NSDictionary *)toDict
{
    return @{
             @"action": self.action,
             @"type": self.type,
             @"value": self.value,
             @"created": self.created
             };
}

// Custom logic goes here.

@end
