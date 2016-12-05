// DO NOT EDIT. This file is machine-generated and constantly overwritten.
// Make changes to Submission.m instead.

#import "_Submission.h"

const struct SubmissionAttributes SubmissionAttributes = {
	.courseUid = @"courseUid",
	.hasLocalChanges = @"hasLocalChanges",
	.isPublished = @"isPublished",
	.localDirectoryName = @"localDirectoryName",
	.openedAtPage = @"openedAtPage",
	.projectId = @"projectId",
	.selectedForModeration = @"selectedForModeration",
	.submissionId = @"submissionId",
	.timeSpentMarking = @"timeSpentMarking",
};

const struct SubmissionRelationships SubmissionRelationships = {
	.annotations = @"annotations",
	.logs = @"logs",
	.marks = @"marks",
};

const struct SubmissionFetchedProperties SubmissionFetchedProperties = {
};

@implementation SubmissionID
@end

@implementation _Submission

+ (id)insertInManagedObjectContext:(NSManagedObjectContext*)moc_ {
	NSParameterAssert(moc_);
	return [NSEntityDescription insertNewObjectForEntityForName:@"Submission" inManagedObjectContext:moc_];
}

+ (NSString*)entityName {
	return @"Submission";
}

+ (NSEntityDescription*)entityInManagedObjectContext:(NSManagedObjectContext*)moc_ {
	NSParameterAssert(moc_);
	return [NSEntityDescription entityForName:@"Submission" inManagedObjectContext:moc_];
}

- (SubmissionID*)objectID {
	return (SubmissionID*)[super objectID];
}

+ (NSSet*)keyPathsForValuesAffectingValueForKey:(NSString*)key {
	NSSet *keyPaths = [super keyPathsForValuesAffectingValueForKey:key];
	
	if ([key isEqualToString:@"hasLocalChangesValue"]) {
		NSSet *affectingKey = [NSSet setWithObject:@"hasLocalChanges"];
		keyPaths = [keyPaths setByAddingObjectsFromSet:affectingKey];
		return keyPaths;
	}
	if ([key isEqualToString:@"isPublishedValue"]) {
		NSSet *affectingKey = [NSSet setWithObject:@"isPublished"];
		keyPaths = [keyPaths setByAddingObjectsFromSet:affectingKey];
		return keyPaths;
	}
	if ([key isEqualToString:@"openedAtPageValue"]) {
		NSSet *affectingKey = [NSSet setWithObject:@"openedAtPage"];
		keyPaths = [keyPaths setByAddingObjectsFromSet:affectingKey];
		return keyPaths;
	}
	if ([key isEqualToString:@"selectedForModerationValue"]) {
		NSSet *affectingKey = [NSSet setWithObject:@"selectedForModeration"];
		keyPaths = [keyPaths setByAddingObjectsFromSet:affectingKey];
		return keyPaths;
	}
	if ([key isEqualToString:@"submissionIdValue"]) {
		NSSet *affectingKey = [NSSet setWithObject:@"submissionId"];
		keyPaths = [keyPaths setByAddingObjectsFromSet:affectingKey];
		return keyPaths;
	}
	if ([key isEqualToString:@"timeSpentMarkingValue"]) {
		NSSet *affectingKey = [NSSet setWithObject:@"timeSpentMarking"];
		keyPaths = [keyPaths setByAddingObjectsFromSet:affectingKey];
		return keyPaths;
	}

	return keyPaths;
}




@dynamic courseUid;






@dynamic hasLocalChanges;



- (BOOL)hasLocalChangesValue {
	NSNumber *result = [self hasLocalChanges];
	return [result boolValue];
}

- (void)setHasLocalChangesValue:(BOOL)value_ {
	[self setHasLocalChanges:[NSNumber numberWithBool:value_]];
}

- (BOOL)primitiveHasLocalChangesValue {
	NSNumber *result = [self primitiveHasLocalChanges];
	return [result boolValue];
}

- (void)setPrimitiveHasLocalChangesValue:(BOOL)value_ {
	[self setPrimitiveHasLocalChanges:[NSNumber numberWithBool:value_]];
}





@dynamic isPublished;



- (BOOL)isPublishedValue {
	NSNumber *result = [self isPublished];
	return [result boolValue];
}

- (void)setIsPublishedValue:(BOOL)value_ {
	[self setIsPublished:[NSNumber numberWithBool:value_]];
}

- (BOOL)primitiveIsPublishedValue {
	NSNumber *result = [self primitiveIsPublished];
	return [result boolValue];
}

- (void)setPrimitiveIsPublishedValue:(BOOL)value_ {
	[self setPrimitiveIsPublished:[NSNumber numberWithBool:value_]];
}





@dynamic localDirectoryName;






@dynamic openedAtPage;



- (int16_t)openedAtPageValue {
	NSNumber *result = [self openedAtPage];
	return [result shortValue];
}

- (void)setOpenedAtPageValue:(int16_t)value_ {
	[self setOpenedAtPage:[NSNumber numberWithShort:value_]];
}

- (int16_t)primitiveOpenedAtPageValue {
	NSNumber *result = [self primitiveOpenedAtPage];
	return [result shortValue];
}

- (void)setPrimitiveOpenedAtPageValue:(int16_t)value_ {
	[self setPrimitiveOpenedAtPage:[NSNumber numberWithShort:value_]];
}





@dynamic projectId;






@dynamic selectedForModeration;



- (BOOL)selectedForModerationValue {
	NSNumber *result = [self selectedForModeration];
	return [result boolValue];
}

- (void)setSelectedForModerationValue:(BOOL)value_ {
	[self setSelectedForModeration:[NSNumber numberWithBool:value_]];
}

- (BOOL)primitiveSelectedForModerationValue {
	NSNumber *result = [self primitiveSelectedForModeration];
	return [result boolValue];
}

- (void)setPrimitiveSelectedForModerationValue:(BOOL)value_ {
	[self setPrimitiveSelectedForModeration:[NSNumber numberWithBool:value_]];
}





@dynamic submissionId;



- (int64_t)submissionIdValue {
	NSNumber *result = [self submissionId];
	return [result longLongValue];
}

- (void)setSubmissionIdValue:(int64_t)value_ {
	[self setSubmissionId:[NSNumber numberWithLongLong:value_]];
}

- (int64_t)primitiveSubmissionIdValue {
	NSNumber *result = [self primitiveSubmissionId];
	return [result longLongValue];
}

- (void)setPrimitiveSubmissionIdValue:(int64_t)value_ {
	[self setPrimitiveSubmissionId:[NSNumber numberWithLongLong:value_]];
}





@dynamic timeSpentMarking;



- (double)timeSpentMarkingValue {
	NSNumber *result = [self timeSpentMarking];
	return [result doubleValue];
}

- (void)setTimeSpentMarkingValue:(double)value_ {
	[self setTimeSpentMarking:[NSNumber numberWithDouble:value_]];
}

- (double)primitiveTimeSpentMarkingValue {
	NSNumber *result = [self primitiveTimeSpentMarking];
	return [result doubleValue];
}

- (void)setPrimitiveTimeSpentMarkingValue:(double)value_ {
	[self setPrimitiveTimeSpentMarking:[NSNumber numberWithDouble:value_]];
}





@dynamic annotations;

	
- (NSMutableSet*)annotationsSet {
	[self willAccessValueForKey:@"annotations"];
  
	NSMutableSet *result = (NSMutableSet*)[self mutableSetValueForKey:@"annotations"];
  
	[self didAccessValueForKey:@"annotations"];
	return result;
}
	

@dynamic logs;

	
- (NSMutableSet*)logsSet {
	[self willAccessValueForKey:@"logs"];
  
	NSMutableSet *result = (NSMutableSet*)[self mutableSetValueForKey:@"logs"];
  
	[self didAccessValueForKey:@"logs"];
	return result;
}
	

@dynamic marks;

	
- (NSMutableSet*)marksSet {
	[self willAccessValueForKey:@"marks"];
  
	NSMutableSet *result = (NSMutableSet*)[self mutableSetValueForKey:@"marks"];
  
	[self didAccessValueForKey:@"marks"];
	return result;
}
	






@end
