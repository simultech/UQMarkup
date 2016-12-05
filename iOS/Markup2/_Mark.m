// DO NOT EDIT. This file is machine-generated and constantly overwritten.
// Make changes to Mark.m instead.

#import "_Mark.h"

const struct MarkAttributes MarkAttributes = {
	.projectId = @"projectId",
	.rubricId = @"rubricId",
	.value = @"value",
};

const struct MarkRelationships MarkRelationships = {
	.submission = @"submission",
};

const struct MarkFetchedProperties MarkFetchedProperties = {
};

@implementation MarkID
@end

@implementation _Mark

+ (id)insertInManagedObjectContext:(NSManagedObjectContext*)moc_ {
	NSParameterAssert(moc_);
	return [NSEntityDescription insertNewObjectForEntityForName:@"Mark" inManagedObjectContext:moc_];
}

+ (NSString*)entityName {
	return @"Mark";
}

+ (NSEntityDescription*)entityInManagedObjectContext:(NSManagedObjectContext*)moc_ {
	NSParameterAssert(moc_);
	return [NSEntityDescription entityForName:@"Mark" inManagedObjectContext:moc_];
}

- (MarkID*)objectID {
	return (MarkID*)[super objectID];
}

+ (NSSet*)keyPathsForValuesAffectingValueForKey:(NSString*)key {
	NSSet *keyPaths = [super keyPathsForValuesAffectingValueForKey:key];
	
	if ([key isEqualToString:@"projectIdValue"]) {
		NSSet *affectingKey = [NSSet setWithObject:@"projectId"];
		keyPaths = [keyPaths setByAddingObjectsFromSet:affectingKey];
		return keyPaths;
	}
	if ([key isEqualToString:@"rubricIdValue"]) {
		NSSet *affectingKey = [NSSet setWithObject:@"rubricId"];
		keyPaths = [keyPaths setByAddingObjectsFromSet:affectingKey];
		return keyPaths;
	}

	return keyPaths;
}




@dynamic projectId;



- (int32_t)projectIdValue {
	NSNumber *result = [self projectId];
	return [result intValue];
}

- (void)setProjectIdValue:(int32_t)value_ {
	[self setProjectId:[NSNumber numberWithInt:value_]];
}

- (int32_t)primitiveProjectIdValue {
	NSNumber *result = [self primitiveProjectId];
	return [result intValue];
}

- (void)setPrimitiveProjectIdValue:(int32_t)value_ {
	[self setPrimitiveProjectId:[NSNumber numberWithInt:value_]];
}





@dynamic rubricId;



- (int32_t)rubricIdValue {
	NSNumber *result = [self rubricId];
	return [result intValue];
}

- (void)setRubricIdValue:(int32_t)value_ {
	[self setRubricId:[NSNumber numberWithInt:value_]];
}

- (int32_t)primitiveRubricIdValue {
	NSNumber *result = [self primitiveRubricId];
	return [result intValue];
}

- (void)setPrimitiveRubricIdValue:(int32_t)value_ {
	[self setPrimitiveRubricId:[NSNumber numberWithInt:value_]];
}





@dynamic value;






@dynamic submission;

	






@end
