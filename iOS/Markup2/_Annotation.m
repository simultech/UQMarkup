// DO NOT EDIT. This file is machine-generated and constantly overwritten.
// Make changes to Annotation.m instead.

#import "_Annotation.h"

const struct AnnotationAttributes AnnotationAttributes = {
	.annotationType = @"annotationType",
	.colour = @"colour",
	.height = @"height",
	.layer = @"layer",
	.localFileName = @"localFileName",
	.pageNumber = @"pageNumber",
	.title = @"title",
	.width = @"width",
	.xPos = @"xPos",
	.yPos = @"yPos",
};

const struct AnnotationRelationships AnnotationRelationships = {
	.submission = @"submission",
};

const struct AnnotationFetchedProperties AnnotationFetchedProperties = {
};

@implementation AnnotationID
@end

@implementation _Annotation

+ (id)insertInManagedObjectContext:(NSManagedObjectContext*)moc_ {
	NSParameterAssert(moc_);
	return [NSEntityDescription insertNewObjectForEntityForName:@"Annotation" inManagedObjectContext:moc_];
}

+ (NSString*)entityName {
	return @"Annotation";
}

+ (NSEntityDescription*)entityInManagedObjectContext:(NSManagedObjectContext*)moc_ {
	NSParameterAssert(moc_);
	return [NSEntityDescription entityForName:@"Annotation" inManagedObjectContext:moc_];
}

- (AnnotationID*)objectID {
	return (AnnotationID*)[super objectID];
}

+ (NSSet*)keyPathsForValuesAffectingValueForKey:(NSString*)key {
	NSSet *keyPaths = [super keyPathsForValuesAffectingValueForKey:key];
	
	if ([key isEqualToString:@"heightValue"]) {
		NSSet *affectingKey = [NSSet setWithObject:@"height"];
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





@dynamic layer;






@dynamic localFileName;






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





@dynamic submission;

	






@end
