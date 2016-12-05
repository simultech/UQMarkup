// DO NOT EDIT. This file is machine-generated and constantly overwritten.
// Make changes to LibraryAnnotation.m instead.

#import "_LibraryAnnotation.h"

const struct LibraryAnnotationAttributes LibraryAnnotationAttributes = {
	.annotationType = @"annotationType",
	.colour = @"colour",
	.height = @"height",
	.inLibrary = @"inLibrary",
	.localFileName = @"localFileName",
	.orderIndex = @"orderIndex",
	.pageNumber = @"pageNumber",
	.title = @"title",
	.width = @"width",
	.xPos = @"xPos",
	.yPos = @"yPos",
};

const struct LibraryAnnotationRelationships LibraryAnnotationRelationships = {
};

const struct LibraryAnnotationFetchedProperties LibraryAnnotationFetchedProperties = {
};

@implementation LibraryAnnotationID
@end

@implementation _LibraryAnnotation

+ (id)insertInManagedObjectContext:(NSManagedObjectContext*)moc_ {
	NSParameterAssert(moc_);
	return [NSEntityDescription insertNewObjectForEntityForName:@"LibraryAnnotation" inManagedObjectContext:moc_];
}

+ (NSString*)entityName {
	return @"LibraryAnnotation";
}

+ (NSEntityDescription*)entityInManagedObjectContext:(NSManagedObjectContext*)moc_ {
	NSParameterAssert(moc_);
	return [NSEntityDescription entityForName:@"LibraryAnnotation" inManagedObjectContext:moc_];
}

- (LibraryAnnotationID*)objectID {
	return (LibraryAnnotationID*)[super objectID];
}

+ (NSSet*)keyPathsForValuesAffectingValueForKey:(NSString*)key {
	NSSet *keyPaths = [super keyPathsForValuesAffectingValueForKey:key];
	
	if ([key isEqualToString:@"heightValue"]) {
		NSSet *affectingKey = [NSSet setWithObject:@"height"];
		keyPaths = [keyPaths setByAddingObjectsFromSet:affectingKey];
		return keyPaths;
	}
	if ([key isEqualToString:@"inLibraryValue"]) {
		NSSet *affectingKey = [NSSet setWithObject:@"inLibrary"];
		keyPaths = [keyPaths setByAddingObjectsFromSet:affectingKey];
		return keyPaths;
	}
	if ([key isEqualToString:@"orderIndexValue"]) {
		NSSet *affectingKey = [NSSet setWithObject:@"orderIndex"];
		keyPaths = [keyPaths setByAddingObjectsFromSet:affectingKey];
		return keyPaths;
	}
	if ([key isEqualToString:@"pageNumberValue"]) {
		NSSet *affectingKey = [NSSet setWithObject:@"pageNumber"];
		keyPaths = [keyPaths setByAddingObjectsFromSet:affectingKey];
		return keyPaths;
	}
	if ([key isEqualToString:@"widthValue"]) {
		NSSet *affectingKey = [NSSet setWithObject:@"width"];
		keyPaths = [keyPaths setByAddingObjectsFromSet:affectingKey];
		return keyPaths;
	}
	if ([key isEqualToString:@"xPosValue"]) {
		NSSet *affectingKey = [NSSet setWithObject:@"xPos"];
		keyPaths = [keyPaths setByAddingObjectsFromSet:affectingKey];
		return keyPaths;
	}
	if ([key isEqualToString:@"yPosValue"]) {
		NSSet *affectingKey = [NSSet setWithObject:@"yPos"];
		keyPaths = [keyPaths setByAddingObjectsFromSet:affectingKey];
		return keyPaths;
	}

	return keyPaths;
}




@dynamic annotationType;






@dynamic colour;






@dynamic height;



- (float)heightValue {
	NSNumber *result = [self height];
	return [result floatValue];
}

- (void)setHeightValue:(float)value_ {
	[self setHeight:[NSNumber numberWithFloat:value_]];
}

- (float)primitiveHeightValue {
	NSNumber *result = [self primitiveHeight];
	return [result floatValue];
}

- (void)setPrimitiveHeightValue:(float)value_ {
	[self setPrimitiveHeight:[NSNumber numberWithFloat:value_]];
}





@dynamic inLibrary;



- (BOOL)inLibraryValue {
	NSNumber *result = [self inLibrary];
	return [result boolValue];
}

- (void)setInLibraryValue:(BOOL)value_ {
	[self setInLibrary:[NSNumber numberWithBool:value_]];
}

- (BOOL)primitiveInLibraryValue {
	NSNumber *result = [self primitiveInLibrary];
	return [result boolValue];
}

- (void)setPrimitiveInLibraryValue:(BOOL)value_ {
	[self setPrimitiveInLibrary:[NSNumber numberWithBool:value_]];
}





@dynamic localFileName;






@dynamic orderIndex;



- (int16_t)orderIndexValue {
	NSNumber *result = [self orderIndex];
	return [result shortValue];
}

- (void)setOrderIndexValue:(int16_t)value_ {
	[self setOrderIndex:[NSNumber numberWithShort:value_]];
}

- (int16_t)primitiveOrderIndexValue {
	NSNumber *result = [self primitiveOrderIndex];
	return [result shortValue];
}

- (void)setPrimitiveOrderIndexValue:(int16_t)value_ {
	[self setPrimitiveOrderIndex:[NSNumber numberWithShort:value_]];
}





@dynamic pageNumber;



- (int16_t)pageNumberValue {
	NSNumber *result = [self pageNumber];
	return [result shortValue];
}

- (void)setPageNumberValue:(int16_t)value_ {
	[self setPageNumber:[NSNumber numberWithShort:value_]];
}

- (int16_t)primitivePageNumberValue {
	NSNumber *result = [self primitivePageNumber];
	return [result shortValue];
}

- (void)setPrimitivePageNumberValue:(int16_t)value_ {
	[self setPrimitivePageNumber:[NSNumber numberWithShort:value_]];
}





@dynamic title;






@dynamic width;



- (float)widthValue {
	NSNumber *result = [self width];
	return [result floatValue];
}

- (void)setWidthValue:(float)value_ {
	[self setWidth:[NSNumber numberWithFloat:value_]];
}

- (float)primitiveWidthValue {
	NSNumber *result = [self primitiveWidth];
	return [result floatValue];
}

- (void)setPrimitiveWidthValue:(float)value_ {
	[self setPrimitiveWidth:[NSNumber numberWithFloat:value_]];
}





@dynamic xPos;



- (float)xPosValue {
	NSNumber *result = [self xPos];
	return [result floatValue];
}

- (void)setXPosValue:(float)value_ {
	[self setXPos:[NSNumber numberWithFloat:value_]];
}

- (float)primitiveXPosValue {
	NSNumber *result = [self primitiveXPos];
	return [result floatValue];
}

- (void)setPrimitiveXPosValue:(float)value_ {
	[self setPrimitiveXPos:[NSNumber numberWithFloat:value_]];
}





@dynamic yPos;



- (float)yPosValue {
	NSNumber *result = [self yPos];
	return [result floatValue];
}

- (void)setYPosValue:(float)value_ {
	[self setYPos:[NSNumber numberWithFloat:value_]];
}

- (float)primitiveYPosValue {
	NSNumber *result = [self primitiveYPos];
	return [result floatValue];
}

- (void)setPrimitiveYPosValue:(float)value_ {
	[self setPrimitiveYPos:[NSNumber numberWithFloat:value_]];
}










@end
