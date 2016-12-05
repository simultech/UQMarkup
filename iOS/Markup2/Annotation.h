#import "_Annotation.h"

typedef enum {
    AnnotationTypeNone,
    AnnotationTypeText,
    AnnotationTypeFreehand,
    AnnotationTypeHighlight,
    AnnotationTypeErase,
    AnnotationTypeRecording
} AnnotationType;

@interface Annotation : _Annotation {}
// Custom logic goes here.
- (BOOL)isReallyEqual:(Annotation *)other;
- (NSString *)localFilePath;
- (NSDictionary *)toDict;
- (void)deleteLocalFile;
+ (NSString *)timestampString;
@end
