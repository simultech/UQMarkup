// DO NOT EDIT. This file is machine-generated and constantly overwritten.
// Make changes to Mark.h instead.

#import <CoreData/CoreData.h>


extern const struct MarkAttributes {
	__unsafe_unretained NSString *projectId;
	__unsafe_unretained NSString *rubricId;
	__unsafe_unretained NSString *value;
} MarkAttributes;

extern const struct MarkRelationships {
	__unsafe_unretained NSString *submission;
} MarkRelationships;

extern const struct MarkFetchedProperties {
} MarkFetchedProperties;

@class Submission;





@interface MarkID : NSManagedObjectID {}
@end

@interface _Mark : NSManagedObject {}
+ (id)insertInManagedObjectContext:(NSManagedObjectContext*)moc_;
+ (NSString*)entityName;
+ (NSEntityDescription*)entityInManagedObjectContext:(NSManagedObjectContext*)moc_;
- (MarkID*)objectID;





@property (nonatomic, strong) NSNumber* projectId;



@property int32_t projectIdValue;
- (int32_t)projectIdValue;
- (void)setProjectIdValue:(int32_t)value_;

//- (BOOL)validateProjectId:(id*)value_ error:(NSError**)error_;





@property (nonatomic, strong) NSNumber* rubricId;



@property int32_t rubricIdValue;
- (int32_t)rubricIdValue;
- (void)setRubricIdValue:(int32_t)value_;

//- (BOOL)validateRubricId:(id*)value_ error:(NSError**)error_;





@property (nonatomic, strong) NSString* value;



//- (BOOL)validateValue:(id*)value_ error:(NSError**)error_;





@property (nonatomic, strong) Submission *submission;

//- (BOOL)validateSubmission:(id*)value_ error:(NSError**)error_;





@end

@interface _Mark (CoreDataGeneratedAccessors)

@end

@interface _Mark (CoreDataGeneratedPrimitiveAccessors)


- (NSNumber*)primitiveProjectId;
- (void)setPrimitiveProjectId:(NSNumber*)value;

- (int32_t)primitiveProjectIdValue;
- (void)setPrimitiveProjectIdValue:(int32_t)value_;




- (NSNumber*)primitiveRubricId;
- (void)setPrimitiveRubricId:(NSNumber*)value;

- (int32_t)primitiveRubricIdValue;
- (void)setPrimitiveRubricIdValue:(int32_t)value_;




- (NSString*)primitiveValue;
- (void)setPrimitiveValue:(NSString*)value;





- (Submission*)primitiveSubmission;
- (void)setPrimitiveSubmission:(Submission*)value;


@end
