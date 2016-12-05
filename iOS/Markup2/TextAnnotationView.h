//
//  TextAnnotationView.h
//  Markup2
//

#import <UIKit/UIKit.h>

@protocol TextAnnotationViewDelegate;
@interface TextAnnotationView : UIView
@property (nonatomic, weak) id<TextAnnotationViewDelegate> delegate;
@property (nonatomic, strong) UITextView *textView;
- (void)editContents;
- (void)textViewDidEndEditing:(UITextView *)textView;
@end

@protocol TextAnnotationViewDelegate

- (void)textAnnotationView:(TextAnnotationView *)textView willBeginEditingTextAtPoint:(CGPoint)point;
- (void)textAnnotationView:(TextAnnotationView *)textView didFinishEditingTextAtPoint:(CGPoint)point withSize:(CGSize)size updatingExisting:(BOOL)updating;

@end
