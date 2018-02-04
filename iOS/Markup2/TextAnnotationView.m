//
//  TextAnnotationView.m
//  Markup2
//

#import "TextAnnotationView.h"
#import "AnnotationSettingsManager.h"
#import "Annotation.h"
#import "EditableAnnotationView.h"
#import <QuartzCore/QuartzCore.h>

@interface TextAnnotationView () <UITextViewDelegate>

@end

@implementation TextAnnotationView

static int const autoCorrectHeightAddition = 20;

- (id)initWithFrame:(CGRect)frame
{
    self = [super initWithFrame:frame];
    if (self) {
        // Initialization code
        CGRect tvF = CGRectMake(0.0, 0.0, frame.size.width, frame.size.height+autoCorrectHeightAddition);
        self.textView = [[UITextView alloc] initWithFrame:tvF];
        [self.textView setAutocorrectionType:UITextAutocorrectionTypeNo];
        [self.textView setTextColor:[[AnnotationSettingsManager sharedManager] textColor]];
        [self.textView setBackgroundColor:[UIColor clearColor]];
        [self.textView setFont:[UIFont systemFontOfSize:14.0]];
        self.textView.delegate = self;
        [self.textView setClipsToBounds:NO];
        [self addSubview:self.textView];
    }
    return self;
}

- (void)editContents
{
    DLog(@"%@", NSStringFromCGRect(self.textView.frame));
    self.textView.editable = YES;
    [self.textView becomeFirstResponder];
    [self.textView setEnablesReturnKeyAutomatically:YES];
}

- (void)resizeToFitText
{
    self.textView.frame = CGRectMake(self.textView.frame.origin.x, self.textView.frame.origin.y, self.textView.contentSize.width, self.textView.contentSize.height+autoCorrectHeightAddition);
    self.frame = CGRectMake(self.frame.origin.x, self.frame.origin.y, self.textView.contentSize.width, self.textView.contentSize.height+autoCorrectHeightAddition);
}

#pragma mark UITextViewDelegate methods

- (void)textViewDidChange:(UITextView *)textView
{
    [self.textView setTextColor:[[AnnotationSettingsManager sharedManager] textColor]];
    [self resizeToFitText];
    if(textView.text.length > 0) {
        if([textView.text characterAtIndex:textView.text.length-1] == 10) {
            textView.text = [textView.text stringByAppendingFormat:@" "];
            textView.text = [textView.text substringToIndex:textView.text.length-1];
        }
    }
}

- (void)textViewDidBeginEditing:(UITextView *)textView
{
    if ([self.superview isKindOfClass:[EditableAnnotationView class]]) {
        EditableAnnotationView *container = (EditableAnnotationView *)self.superview;
        [container setAnnotationSelected:NO];
    }
    CGPoint inputTop = self.inputView.frame.origin;
    if (self.frame.origin.y > inputTop.y) {
        
    }
}

- (void)textViewDidEndEditing:(UITextView *)textView
{
    NSLog(@"FINISHED");
    NSString *trimmedString = [self.textView.text stringByTrimmingCharactersInSet:[NSCharacterSet whitespaceAndNewlineCharacterSet]];
    self.textView.editable = NO;
    if(![trimmedString isEqualToString:@""]) {
        CGSize realFrameSize = self.frame.size;
        NSArray *linesOfText = [textView.text componentsSeparatedByString:@"\n"];
        int maxLineWidth = 0;
        for(NSString *lineOfText in linesOfText) {
            NSMutableParagraphStyle *textStyle = [[NSMutableParagraphStyle defaultParagraphStyle] mutableCopy];
            textStyle.lineBreakMode = NSLineBreakByWordWrapping;
            NSDictionary *dict = @{ NSFontAttributeName: textView.font, NSParagraphStyleAttributeName: textStyle};
            CGRect newFrameRect = [lineOfText boundingRectWithSize:CGSizeMake(textView.frame.size.width, MAXFLOAT)
                                                      options:NSStringDrawingUsesLineFragmentOrigin
                                                   attributes:dict
                                                      context:nil];
            
            CGSize newFrameSize = newFrameRect.size;
            if(newFrameSize.width > maxLineWidth) {
                maxLineWidth = newFrameSize.width;
            }
        }
        realFrameSize.width = maxLineWidth+20;
        realFrameSize.height -= autoCorrectHeightAddition;
        if ([self.superview isKindOfClass:[EditableAnnotationView class]]) {
            EditableAnnotationView *container = (EditableAnnotationView *)self.superview;
            Annotation *ann = container.annotation;
            ann.title = self.textView.text;
            [[NSManagedObjectContext defaultContext] save];
            [self.delegate textAnnotationView:self didFinishEditingTextAtPoint:CGPointMake(container.frame.origin.x + kBGMargin, container.frame.origin.y + kBGMargin) withSize:realFrameSize updatingExisting:YES];
        } else {
            [self.delegate textAnnotationView:self didFinishEditingTextAtPoint:self.frame.origin withSize:realFrameSize updatingExisting:NO];
        }
    
    } else {
        [textView removeFromSuperview];
    }
}

@end
