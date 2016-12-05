//
//  Course.h
//  Markup2
//

#import <Foundation/Foundation.h>

@interface Course : NSObject <NSCoding>
@property (nonatomic, strong) NSString *courseId;
@property (nonatomic, strong) NSString *courseCode;
@property (nonatomic, strong) NSString *shadowCode;
@property (nonatomic, strong) NSString *courseName;
@property (nonatomic, strong) NSString *year;
@property (nonatomic, strong) NSString *semester;
@end
