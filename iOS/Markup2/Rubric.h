//
//  Rubric.h
//  Markup2
//

#import <Foundation/Foundation.h>

typedef enum {
    RubricTypeTable,
    RubricTypeNumber,
    RubricTypeBoolean,
    RubricTypeText
} RubricType;

@interface Rubric : NSObject <NSCoding, NSCopying>
@property (nonatomic, assign) NSInteger projectId;
@property (nonatomic, assign) NSInteger rubricId;
@property (nonatomic, strong) NSString *rubricName;
@property (nonatomic, assign) RubricType rubricType;
@property (nonatomic, strong) NSString *rubricSection;
@property (nonatomic, strong) NSString *rubricValue;
@property (nonatomic, strong) NSDictionary *rubricMeta;

@end
