//
//  AnnotationLibraryCell.m
//  Markup2
//

#import "AnnotationLibraryCell.h"
#import "LibraryAnnotation.h"
#import <QuartzCore/QuartzCore.h>

@interface AnnotationLibraryCell ()

@property (nonatomic, strong) UILongPressGestureRecognizer *hold;
@end

@implementation AnnotationLibraryCell

- (id)initWithCoder:(NSCoder *)aDecoder
{
    self = [super initWithCoder:aDecoder];
    if (self) {
        
    }
    return self;
}

- (void)setSelected:(BOOL)selected animated:(BOOL)animated
{
    [super setSelected:selected animated:animated];
    // Configure the view for the selected state
}

@end
