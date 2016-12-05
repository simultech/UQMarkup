// DO NOT EDIT. This file is machine-generated and constantly overwritten.
// Make changes to LibraryAnnotation.h instead.

#import <CoreData/CoreData.h>


extern const struct LibraryAnnotationAttributes {
	__unsafe_unretained NSString *annotationType;
	__unsafe_unretained NSString *colour;
	__unsafe_unretained NSString *height;
	__unsafe_unretained NSString *inLibrary;
	__unsafe_unretained NSString *localFileName;
	__unsafe_unretained NSString *orderIndex;
	__unsafe_unretained NSString *pageNumber;
	__unsafe_unretained NSString *title;
	__unsafe_unretained NSString *width;
	__unsafe_unretained NSString *xPos;
	__unsafe_unretained NSString *yPos;
} LibraryAnnotationAttributes;

extern const struct LibraryAnnotationRelationships {
} LibraryAnnotationRelationships;

extern const struct LibraryAnnotationFetchedProperties {
} LibraryAnnotationFetchedProperties;














@interface LibraryAnnotationID : NSManagedObjectID {}
@end

@interface _LibraryAnnotation : NSManagedObject {}
+ (id)insertInManagedObjectContext:(NSManagedObjectContext*)moc_;
+ (NSString*)entityName;
+ (NSEntityDescription*)entityInManagedObjectContext:(NSManagedObjectContext*)moc_;
- (LibraryAnnotationID*)objectID;





@property (nonatomic, strong) NSString* annotationType;



//- (BOOL)validateAnnotationType:(id*)value_ error:(NSError**)error_;





@property (nonatomic, strong) NSString* colour;



//- (BOOL)validateColour:(id*)value_ error:(NSError**)error_;





@property (nonatomic, strong) NSNumber* height;



@property float heightValue;
- (float)heightValue;
- (void)setHeightValue:(float)value_;

//- (BOOL)validateHeight:(id*)value_ error:(NSError**)error_;





@property (nonatomic, strong) NSNumber* inLibrary;



@property BOOL inLibraryValue;
- (BOOL)inLibraryValue;
- (void)setInLibraryValue:(BOOL)value_;

//- (BOOL)validateInLibrary:(id*)value_ error:(NSError**)error_;





@property (nonatomic, strong) NSString* localFileName;



//- (BOOL)validateLocalFileName:(id*)value_ error:(NSError**)error_;





@property (nonatomic, strong) NSNumber* orderIndex;



@property int16_t orderIndexValue;
- (int16_t)orderIndexValue;
- (void)setOrderIndexValue:(int16_t)value_;

//- (BOOL)validateOrderIndex:(id*)value_ error:(NSError**)error_;





@property (nonatomic, strong) NSNumber* pageNumber;



@property int16_t pageNumberValue;
- (int16_t)pageNumberValue;
- (void)setPageNumberValue:(int16_t)value_;

//- (BOOL)validatePageNumber:(id*)value_ error:(NSError**)error_;





@property (nonatomic, strong) NSString* title;



//- (BOOL)validateTitle:(id*)value_ error:(NSError**)error_;





@property (nonatomic, strong) NSNumber* width;



@property float widthValue;
- (float)widthValue;
- (void)setWidthValue:(float)value_;

//- (BOOL)validateWidth:(id*)value_ error:(NSError**)error_;





@property (nonatomic, strong) NSNumber* xPos;



@property float xPosValue;
- (float)xPosValue;
- (void)setXPosValue:(float)value_;

//- (BOOL)validateXPos:(id*)value_ error:(NSError**)error_;





@property (nonatomic, strong) NSNumber* yPos;



@property float yPosValue;
- (float)yPosValue;
- (void)setYPosValue:(float)value_;

//- (BOOL)validateYPos:(id*)value_ error:(NSError**)error_;






@end

@interface _LibraryAnnotation (CoreDataGeneratedAccessors)

@end

@interface _LibraryAnnotation (CoreDataGeneratedPrimitiveAccessors)


- (NSString*)primitiveAnnotationType;
- (void)setPrimitiveAnnotationType:(NSString*)value;




- (NSString*)primitiveColour;
- (void)setPrimitiveColour:(NSString*)value;




- (NSNumber*)primitiveHeight;
- (void)setPrimitiveHeight:(NSNumber*)value;

- (float)primitiveHeightValue;
- (void)setPrimitiveHeightValue:(float)value_;




- (NSNumber*)primitiveInLibrary;
- (void)setPrimitiveInLibrary:(NSNumber*)value;

- (BOOL)primitiveInLibraryValue;
- (void)setPrimitiveInLibraryValue:(BOOL)value_;




- (NSString*)primitiveLocalFileName;
- (void)setPrimitiveLocalFileName:(NSString*)value;




- (NSNumber*)primitiveOrderIndex;
- (void)setPrimitiveOrderIndex:(NSNumber*)value;

- (int16_t)primitiveOrderIndexValue;
- (void)setPrimitiveOrderIndexValue:(int16_t)value_;




- (NSNumber*)primitivePageNumber;
- (void)setPrimitivePageNumber:(NSNumber*)value;

- (int16_t)primitivePageNumberValue;
- (void)setPrimitivePageNumberValue:(int16_t)value_;




- (NSString*)primitiveTitle;
- (void)setPrimitiveTitle:(NSString*)value;




- (NSNumber*)primitiveWidth;
- (void)setPrimitiveWidth:(NSNumber*)value;

- (float)primitiveWidthValue;
- (void)setPrimitiveWidthValue:(float)value_;




- (NSNumber*)primitiveXPos;
- (void)setPrimitiveXPos:(NSNumber*)value;

- (float)primitiveXPosValue;
- (void)setPrimitiveXPosValue:(float)value_;




- (NSNumber*)primitiveYPos;
- (void)setPrimitiveYPos:(NSNumber*)value;

- (float)primitiveYPosValue;
- (void)setPrimitiveYPosValue:(float)value_;




@end
