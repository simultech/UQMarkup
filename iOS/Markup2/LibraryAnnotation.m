#import "LibraryAnnotation.h"
#import "Annotation.h"

@implementation LibraryAnnotation

// Custom logic goes here.
- (NSString *)localFilePath
{
    return [[self annotationLibraryPath] stringByAppendingPathComponent:self.localFileName];
}

- (NSString *)annotationLibraryPath
{
    NSString *annotationLibraryPath;
    NSArray *paths = NSSearchPathForDirectoriesInDomains(NSDocumentDirectory, NSUserDomainMask, YES);
    if (paths.count > 0) {
        annotationLibraryPath = [[paths objectAtIndex:0] stringByAppendingPathComponent:@"library"];
        if (![[NSFileManager defaultManager] fileExistsAtPath:annotationLibraryPath]) {
            [[NSFileManager defaultManager] createDirectoryAtPath:annotationLibraryPath withIntermediateDirectories:YES attributes:nil error:nil];
        }
    }
    
    return annotationLibraryPath;
}

- (NSDictionary *)toDict
{
    return @{
    @"type": self.annotationType,
    @"filename": self.localFileName,
    @"title": self.title,
    @"width_percentage": self.width,
    @"height_percentage": self.height
    };
}

- (NSString *)description
{
    return [NSString stringWithFormat:@"Library Annotation:\nannotationType:%@\nfilename:%@\ntitle:%@\n\nwidth:%@\nheight:%@", self.annotationType, self.localFileName, self.title, self.width, self.height];
}

- (void)populateFromAnnotation:(Annotation *)ann
{
    NSFileManager *defaultManager = [NSFileManager defaultManager];
    NSString *ext = [ann.localFileName pathExtension];
    NSPredicate *pred = [NSPredicate predicateWithFormat:@"inLibrary == YES"];
    int annNo = [LibraryAnnotation countOfEntitiesWithPredicate:pred];
    self.annotationType = ann.annotationType;
    self.title = ann.title;
    self.width = ann.width;
    self.height = ann.height;
    self.colour = ann.colour;
    self.inLibrary = @YES;
    NSLog(@"CREATING TYPE %@",ann.annotationType);
    NSString *filename = [NSString stringWithFormat:@"libraryann_%@.%@", [LibraryAnnotation timestampString], ext];
    int i = 1;
    while ([defaultManager fileExistsAtPath:filename]) {
        filename = [NSString stringWithFormat:@"libraryann_%@_%d.%@", [LibraryAnnotation timestampString], i, ext];
        i++;
    }
    self.localFileName = filename;
    self.orderIndex = @(annNo);
    
    if (![defaultManager fileExistsAtPath:[self annotationLibraryPath]]) {
        NSError *dirCreateError;
        if(![ann.annotationType isEqualToString:@"Text"]) {
            [defaultManager createDirectoryAtPath:[self annotationLibraryPath] withIntermediateDirectories:YES attributes:nil error:&dirCreateError];
        }
        if (dirCreateError) {
            DLog(@"%@", dirCreateError);
        }
    }
    
    NSError *copyError;
    if(![ann.annotationType isEqualToString:@"Text"]) {
        [defaultManager copyItemAtPath:[ann localFilePath] toPath:[self localFilePath] error:&copyError];
    }
    
    if (copyError) {
        DLog(@"%@", copyError);
    }
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
    NSError *error;
    [[NSFileManager defaultManager] removeItemAtPath:[self localFilePath] error:&error];
    if (error) {
        NSLog(@"%@", error);
    }
}

- (BOOL)isReallyEqual:(Annotation *)other
{
    if (![self.annotationType isEqualToString:other.annotationType]) {
        return NO;
    }
    if (![self.title isEqualToString:other.title]) {
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

@end
