// DO NOT EDIT. This file is machine-generated and constantly overwritten.
// Make changes to Submission.h instead.

#import <CoreData/CoreData.h>


extern const struct SubmissionAttributes {
	__unsafe_unretained NSString *courseUid;
	__unsafe_unretained NSString *hasLocalChanges;
	__unsafe_unretained NSString *isPublished;
	__unsafe_unretained NSString *localDirectoryName;
	__unsafe_unretained NSString *openedAtPage;
	__unsafe_unretained NSString *projectId;
	__unsafe_unretained NSString *selectedForModeration;
	__unsafe_unretained NSString *submissionId;
	__unsafe_unretained NSString *timeSpentMarking;
} SubmissionAttributes;

extern const struct SubmissionRelationships {
	__unsafe_unretained NSString *annotations;
	__unsafe_unretained NSString *logs;
	__unsafe_unretained NSString *marks;
} SubmissionRelationships;

extern const struct SubmissionFetchedProperties {
} SubmissionFetchedProperties;

@class Annotation;
@class Log;
@class Mark;











@interface SubmissionID : NSManagedObjectID {}
@end

@interface _Submission : NSManagedObject {}
+ (id)insertInManagedObjectContext:(NSManagedObjectContext*)moc_;
+ (NSString*)entityName;
+ (NSEntityDescription*)entityInManagedObjectContext:(NSManagedObjectContext*)moc_;
- (SubmissionID*)objectID;





@property (nonatomic, strong) NSString* courseUid;



//- (BOOL)validateCourseUid:(id*)value_ error:(NSError**)error_;





@property (nonatomic, strong) NSNumber* hasLocalChanges;



@property BOOL hasLocalChangesValue;
- (BOOL)hasLocalChangesValue;
- (void)setHasLocalChangesValue:(BOOL)value_;

//- (BOOL)validateHasLocalChanges:(id*)value_ error:(NSError**)error_;





@property (nonatomic, strong) NSNumber* isPublished;



@property BOOL isPublishedValue;
- (BOOL)isPublishedValue;
- (void)setIsPublishedValue:(BOOL)value_;

//- (BOOL)validateIsPublished:(id*)value_ error:(NSError**)error_;





@property (nonatomic, strong) NSString* localDirectoryName;



//- (BOOL)validateLocalDirectoryName:(id*)value_ error:(NSError**)error_;





@property (nonatomic, strong) NSNumber* openedAtPage;



@property int16_t openedAtPageValue;
- (int16_t)openedAtPageValue;
- (void)setOpenedAtPageValue:(int16_t)value_;

//- (BOOL)validateOpenedAtPage:(id*)value_ error:(NSError**)error_;





@property (nonatomic, strong) NSString* projectId;



//- (BOOL)validateProjectId:(id*)value_ error:(NSError**)error_;





@property (nonatomic, strong) NSNumber* selectedForModeration;



@property BOOL selectedForModerationValue;
- (BOOL)selectedForModerationValue;
- (void)setSelectedForModerationValue:(BOOL)value_;

//- (BOOL)validateSelectedForModeration:(id*)value_ error:(NSError**)error_;





@property (nonatomic, strong) NSNumber* submissionId;



@property int64_t submissionIdValue;
- (int64_t)submissionIdValue;
- (void)setSubmissionIdValue:(int64_t)value_;

//- (BOOL)validateSubmissionId:(id*)value_ error:(NSError**)error_;





@property (nonatomic, strong) NSNumber* timeSpentMarking;



@property double timeSpentMarkingValue;
- (double)timeSpentMarkingValue;
- (void)setTimeSpentMarkingValue:(double)value_;

//- (BOOL)validateTimeSpentMarking:(id*)value_ error:(NSError**)error_;





@property (nonatomic, strong) NSSet *annotations;

- (NSMutableSet*)annotationsSet;




@property (nonatomic, strong) NSSet *logs;

- (NSMutableSet*)logsSet;




@property (nonatomic, strong) NSSet *marks;

- (NSMutableSet*)marksSet;





@end

@interface _Submission (CoreDataGeneratedAccessors)

- (void)addAnnotations:(NSSet*)value_;
- (void)removeAnnotations:(NSSet*)value_;
- (void)addAnnotationsObject:(Annotation*)value_;
- (void)removeAnnotationsObject:(Annotation*)value_;

- (void)addLogs:(NSSet*)value_;
- (void)removeLogs:(NSSet*)value_;
- (void)addLogsObject:(Log*)value_;
- (void)removeLogsObject:(Log*)value_;

- (void)addMarks:(NSSet*)value_;
- (void)removeMarks:(NSSet*)value_;
- (void)addMarksObject:(Mark*)value_;
- (void)removeMarksObject:(Mark*)value_;

@end

@interface _Submission (CoreDataGeneratedPrimitiveAccessors)


- (NSString*)primitiveCourseUid;
- (void)setPrimitiveCourseUid:(NSString*)value;




- (NSNumber*)primitiveHasLocalChanges;
- (void)setPrimitiveHasLocalChanges:(NSNumber*)value;

- (BOOL)primitiveHasLocalChangesValue;
- (void)setPrimitiveHasLocalChangesValue:(BOOL)value_;




- (NSNumber*)primitiveIsPublished;
- (void)setPrimitiveIsPublished:(NSNumber*)value;

- (BOOL)primitiveIsPublishedValue;
- (void)setPrimitiveIsPublishedValue:(BOOL)value_;




- (NSString*)primitiveLocalDirectoryName;
- (void)setPrimitiveLocalDirectoryName:(NSString*)value;




- (NSNumber*)primitiveOpenedAtPage;
- (void)setPrimitiveOpenedAtPage:(NSNumber*)value;

- (int16_t)primitiveOpenedAtPageValue;
- (void)setPrimitiveOpenedAtPageValue:(int16_t)value_;




- (NSString*)primitiveProjectId;
- (void)setPrimitiveProjectId:(NSString*)value;




- (NSNumber*)primitiveSelectedForModeration;
- (void)setPrimitiveSelectedForModeration:(NSNumber*)value;

- (BOOL)primitiveSelectedForModerationValue;
- (void)setPrimitiveSelectedForModerationValue:(BOOL)value_;




- (NSNumber*)primitiveSubmissionId;
- (void)setPrimitiveSubmissionId:(NSNumber*)value;

- (int64_t)primitiveSubmissionIdValue;
- (void)setPrimitiveSubmissionIdValue:(int64_t)value_;




- (NSNumber*)primitiveTimeSpentMarking;
- (void)setPrimitiveTimeSpentMarking:(NSNumber*)value;

- (double)primitiveTimeSpentMarkingValue;
- (void)setPrimitiveTimeSpentMarkingValue:(double)value_;





- (NSMutableSet*)primitiveAnnotations;
- (void)setPrimitiveAnnotations:(NSMutableSet*)value;



- (NSMutableSet*)primitiveLogs;
- (void)setPrimitiveLogs:(NSMutableSet*)value;



- (NSMutableSet*)primitiveMarks;
- (void)setPrimitiveMarks:(NSMutableSet*)value;


@end
