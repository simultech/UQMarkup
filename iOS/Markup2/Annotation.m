#import "Annotation.h"
#import "Submission.h"

@implementation Annotation
- (NSString *)localFilePath
{
    return [[self.submission localAnnotationDirectory] stringByAppendingPathComponent:self.localFileName];
}

- (NSDictionary *)toDict
{
    NSMutableDictionary *dict = [[NSMutableDictionary alloc] init];
    if(self.annotationType) {
        dict = [@{
            @"type": self.annotationType,
            @"title": self.title,
            @"page_no": self.pageNumber,
            @"x_percentage": self.xPos,
            @"y_percentage": self.yPos,
            @"width_percentage": self.width,
            @"height_percentage": self.height
        } mutableCopy];
        
        if (self.localFileName) {
            [dict setObject:self.localFileName forKey:@"filename"];
        }
        
        if (self.colour) {
            [dict setObject:self.colour forKey:@"colour"];
        }
    }
    return dict;
}

- (NSString *)description
{
    return [NSString stringWithFormat:@"Annotation:\nannotationType:%@\nfilename:%@\ntitle:%@\npage_no:%@\nxPos:%@\nyPos:%@\nwidth:%@\nheight:%@", self.annotationType, self.localFileName, self.title, self.pageNumber, self.xPos, self.yPos, self.width, self.height];
}

- (BOOL)isReallyEqual:(Annotation *)other
{
    if (![self.annotationType isEqualToString:other.annotationType]) {
        return NO;
    }
    if (![self.title isEqualToString:other.title]) {
        return NO;
    }
    if ([self respondsToSelector:@selector(pageNumber)] && ![self.pageNumber isEqual:other.pageNumber]) {
        return NO;
    }
    if (![self.xPos isEqual:other.xPos]) {
        return NO;
    }
    if (![self.yPos isEqual:other.yPos]) {
        return NO;
    }
    if (![self.width isEqual:other.width]) {
        return NO;
    }
    if (![self.height isEqual:other.height]) {
        return NO;
    }
    if (self.localFileName && ![self.localFileName isEqual:other.localFileName]) {
        return NO;
    }
    
    return YES;
}

+ (NSString *)timestampString
{
    static NSDateFormatter *df;
    if (!df) {
        df = [[NSDateFormatter alloc] init];
        [df setDateFormat:@"_yyyy_MM_dd_hh_mm_ss"];
    }
    return [df stringFromDate:[NSDate date]];
}

- (void)deleteLocalFile
{
    if([self.annotationType isEqualToString:@"Text"]) {
        return;
    }
    NSError *error;
    [[NSFileManager defaultManager] removeItemAtPath:[self localFilePath] error:&error];
    if (error) {
        NSLog(@"%@", error);
    }
}

@end
