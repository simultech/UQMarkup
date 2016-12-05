//
//  AnnotationManager.m
//  Markup2
//

#import "AnnotationSettingsManager.h"

@implementation AnnotationSettingsManager

+ (AnnotationSettingsManager *)sharedManager
{
    static AnnotationSettingsManager *instance;
    if (!instance) {
        instance = [[AnnotationSettingsManager alloc] init];
        [instance loadSettings];
    }
    
    return instance;
}

- (void)saveSettings
{
    NSUserDefaults *defaults = [NSUserDefaults standardUserDefaults];
    [defaults setObject:@(self.freehandWidth) forKey:@"FreehandWidthKey"];
    [defaults setObject:@(self.highlightWidth) forKey:@"HighlightWidthKey"];
    [defaults setObject:@(self.eraserWidth) forKey:@"EraserWidthKey"];
    NSData *textColourData = [NSKeyedArchiver archivedDataWithRootObject:self.textColor];
    [defaults setObject:textColourData forKey:@"TextColourKey"];
    NSData *freehandColourData = [NSKeyedArchiver archivedDataWithRootObject:self.freehandColor];
    [defaults setObject:freehandColourData forKey:@"FreehandColourKey"];
    NSData *highlightColourData = [NSKeyedArchiver archivedDataWithRootObject:self.highlightColor];
    [defaults setObject:highlightColourData forKey:@"HighlightColourKey"];
    
    [defaults synchronize];
}

- (void)loadSettings
{
    NSUserDefaults *defaults = [NSUserDefaults standardUserDefaults];
    self.freehandWidth = [defaults objectForKey:@"FreehandWidthKey"] ? [[defaults objectForKey:@"FreehandWidthKey"] floatValue] : 3.0;
    self.highlightWidth = [defaults objectForKey:@"HighlightWidthKey"] ? [[defaults objectForKey:@"HighlightWidthKey"] floatValue] : 20.0;
    self.eraserWidth = [defaults objectForKey:@"EraserWidthKey"] ? [[defaults objectForKey:@"EraserWidthKey"] floatValue] : 20.0;
    
    NSData *textColourData = [defaults objectForKey:@"TextColourKey"];
    self.textColor = textColourData ? [NSKeyedUnarchiver unarchiveObjectWithData:textColourData] : [UIColor redColor];
    NSData *freehandColourData = [defaults objectForKey:@"FreehandColourKey"];
    self.freehandColor = freehandColourData ? [NSKeyedUnarchiver unarchiveObjectWithData:freehandColourData] : [UIColor redColor];
    NSData *highlightColourData = [defaults objectForKey:@"HighlightColourKey"];
    self.highlightColor = highlightColourData ? [NSKeyedUnarchiver unarchiveObjectWithData:highlightColourData] : UIColorFromRGB(0x3fff3f);
}

@end
