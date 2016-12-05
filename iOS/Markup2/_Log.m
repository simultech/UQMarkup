// DO NOT EDIT. This file is machine-generated and constantly overwritten.
// Make changes to Log.m instead.

#import "_Log.h"

const struct LogAttributes LogAttributes = {
	.action = @"action",
	.created = @"created",
	.type = @"type",
	.value = @"value",
};

const struct LogRelationships LogRelationships = {
	.submission = @"submission",
};

const struct LogFetchedProperties LogFetchedProperties = {
};

@implementation LogID
@end

@implementation _Log

+ (id)insertInManagedObjectContext:(NSManagedObjectContext*)moc_ {
	NSParameterAssert(moc_);
	return [NSEntityDescription insertNewObjectForEntityForName:@"Log" inManagedObjectContext:moc_];
}

+ (NSString*)entityName {
	return @"Log";
}

+ (NSEntityDescription*)entityInManagedObjectContext:(NSManagedObjectContext*)moc_ {
	NSParameterAssert(moc_);
	return [NSEntityDescription entityForName:@"Log" inManagedObjectContext:moc_];
}

- (LogID*)objectID {
	return (LogID*)[super objectID];
}

+ (NSSet*)keyPathsForValuesAffectingValueForKey:(NSString*)key {
	NSSet *keyPaths = [super keyPathsForValuesAffectingValueForKey:key];
	

	return keyPaths;
}




@dynamic action;






@dynamic created;






@dynamic type;






@dynamic value;






@dynamic submission;

	






@end
