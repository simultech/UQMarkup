// DO NOT EDIT. This file is machine-generated and constantly overwritten.
// Make changes to Log.h instead.

#import <CoreData/CoreData.h>


extern const struct LogAttributes {
	__unsafe_unretained NSString *action;
	__unsafe_unretained NSString *created;
	__unsafe_unretained NSString *type;
	__unsafe_unretained NSString *value;
} LogAttributes;

extern const struct LogRelationships {
	__unsafe_unretained NSString *submission;
} LogRelationships;

extern const struct LogFetchedProperties {
} LogFetchedProperties;

@class Submission;






@interface LogID : NSManagedObjectID {}
@end

@interface _Log : NSManagedObject {}
+ (id)insertInManagedObjectContext:(NSManagedObjectContext*)moc_;
+ (NSString*)entityName;
+ (NSEntityDescription*)entityInManagedObjectContext:(NSManagedObjectContext*)moc_;
- (LogID*)objectID;





@property (nonatomic, strong) NSString* action;



//- (BOOL)validateAction:(id*)value_ error:(NSError**)error_;





@property (nonatomic, strong) NSString* created;



//- (BOOL)validateCreated:(id*)value_ error:(NSError**)error_;





@property (nonatomic, strong) NSString* type;



//- (BOOL)validateType:(id*)value_ error:(NSError**)error_;





@property (nonatomic, strong) NSString* value;



//- (BOOL)validateValue:(id*)value_ error:(NSError**)error_;





@property (nonatomic, strong) Submission *submission;

//- (BOOL)validateSubmission:(id*)value_ error:(NSError**)error_;





@end

@interface _Log (CoreDataGeneratedAccessors)

@end

@interface _Log (CoreDataGeneratedPrimitiveAccessors)


- (NSString*)primitiveAction;
- (void)setPrimitiveAction:(NSString*)value;




- (NSString*)primitiveCreated;
- (void)setPrimitiveCreated:(NSString*)value;




- (NSString*)primitiveType;
- (void)setPrimitiveType:(NSString*)value;




- (NSString*)primitiveValue;
- (void)setPrimitiveValue:(NSString*)value;





- (Submission*)primitiveSubmission;
- (void)setPrimitiveSubmission:(Submission*)value;


@end
