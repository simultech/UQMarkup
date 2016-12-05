#import "_LibraryAnnotation.h"

@class Annotation;
@interface LibraryAnnotation : _LibraryAnnotation {}
// Custom logic goes here.
- (NSString *)localFilePath;
- (NSString *)annotationLibraryPath;
- (void)populateFromAnnotation:(Annotation *)ann;
+ (NSString *)timestampString;
- (void)deleteLocalFile;
- (BOOL)isReallyEqual:(Annotation *)other;
@end
