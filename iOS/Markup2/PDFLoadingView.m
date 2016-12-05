//
//  PDFLoadingView.m
//  Markup2
//

#import "PDFLoadingView.h"

@implementation PDFLoadingView

- (id)initWithFrame:(CGRect)frame
{
    self = [super initWithFrame:frame];
    if (self) {
        [self setBackgroundColor:[UIColor darkGrayColor]];
        [self setupView];
    }
    return self;
}

- (void) setupView {
    self.alpha = 0.9;
    self.layer.cornerRadius = 9.0;
    self.loadingText = [[UILabel alloc] initWithFrame:CGRectMake(0, 0, self.frame.size.width, 80)];
    [self.loadingText setText:@"Loading Submission"];
    [self.loadingText setTextColor:[UIColor whiteColor]];
    [self.loadingText setBackgroundColor:[UIColor clearColor]];
    [self.loadingText setFont:[UIFont systemFontOfSize:22.0]];
    [self.loadingText setTextAlignment:NSTextAlignmentCenter];
    [self addSubview:self.loadingText];
    self.spinner = [[UIActivityIndicatorView alloc] initWithActivityIndicatorStyle:UIActivityIndicatorViewStyleWhiteLarge];
    self.spinner.center = CGPointMake(self.frame.size.width/2,self.frame.size.height-40);
    [self addSubview:self.spinner];
    [self.spinner startAnimating];
}

- (void) fadeOut {
    [UIView animateWithDuration:0.5f animations:^{
        // fade out effect
        self.alpha = 0.0f;
    } completion:^(BOOL success){
        [self.spinner stopAnimating];
    }];
}

/*
// Only override drawRect: if you perform custom drawing.
// An empty implementation adversely affects performance during animation.
- (void)drawRect:(CGRect)rect
{
    // Drawing code
}
*/

@end
