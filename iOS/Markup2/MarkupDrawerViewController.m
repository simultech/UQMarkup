//
//  MarkupPulloutViewController.m
//  Markup2
//

#import "MarkupDrawerViewController.h"
#import <QuartzCore/QuartzCore.h>
#import "PassthroughView.h"

@interface MarkupDrawerViewController ()
@property (nonatomic, assign) CGRect openFrame;
@property (nonatomic, assign) CGRect closedFrame;
@end

@implementation MarkupDrawerViewController {
    BOOL _isOpen;
    float _tabVerticalPercentage;
    float _contentWidth;
    CGRect _shadowFrame;
    NSString *_tabImageName;
}

- (void)viewDidLoad
{
    [super viewDidLoad];
	// Do any additional setup after loading the view.
    self.view.autoresizesSubviews = YES;
    self.view.autoresizingMask = UIViewAutoresizingFlexibleHeight;
    [self setupContentContainment];
    [self setupInteraction];
    
    [self setupTab];
    [self positionTab];
    [self setupToolbar];
    
}

- (void)setupTab
{
    UIImageView *tmp = [[UIImageView alloc] initWithImage:[UIImage imageNamed:_tabImageName]];
    [self.tabView addSubview:tmp];
    NSLog(@"Tab frame: %@, Image frame: %@", NSStringFromCGRect(self.tabView.frame), NSStringFromCGRect(tmp.frame));
    [self.tabView setBackgroundColor:[UIColor clearColor]];
}

- (void)setupToolbar {
    if (self.onLeft) {
        self.toolbar = [[UIToolbar alloc] initWithFrame:CGRectMake(0, 0, self.contentViewController.view.frame.size.width, 44)];
    } else {
        self.toolbar = [[UIToolbar alloc] initWithFrame:CGRectMake(self.tabView.frame.size.width, 0.0, self.contentViewController.view.frame.size.width, 44.0)];
    }
    [self.toolbar setTintColor:[UIColor darkGrayColor]];
    [self.view addSubview:self.toolbar];
}

- (void)positionTab
{
    DLog(@"%f %f",self.view.frame.size.height,self.view.superview.frame.size.height);
    CGFloat verticalPosition = self.view.frame.size.height * _tabVerticalPercentage;
    if (self.onLeft) {
        self.tabView.frame = CGRectMake(self.tabView.frame.origin.x, verticalPosition, self.tabView.frame.size.width, self.tabView.frame.size.height);
        NSLog(@"%@", NSStringFromCGRect(self.tabView.frame));
    } else {
        self.tabView.frame = CGRectMake(0.0, verticalPosition, self.tabView.frame.size.width, self.tabView.frame.size.height);
        NSLog(@"%@", NSStringFromCGRect(self.tabView.frame));
        NSLog(@"%@", NSStringFromCGRect(self.view.frame));
    }
    [self.tabView setNeedsLayout];
}

- (void)setupContentContainment
{
    CGFloat width;
    if (UIDeviceOrientationIsLandscape([UIApplication sharedApplication].statusBarOrientation)) {
        self.contentFrame = CGRectMake(0.0, 0.0, _contentWidth, self.view.frame.size.height-160);
        width = 1024.0;
    } else {
        self.contentFrame = CGRectMake(0.0, 0.0, _contentWidth, self.view.frame.size.height-160);
        width = 768.0;
    }
    
    if (!self.onLeft) {
        self.view.autoresizingMask = UIViewAutoresizingFlexibleLeftMargin | UIViewAutoresizingFlexibleHeight;
    }

    if (self.onLeft) {
        self.openFrame = CGRectMake(-3.0, 88.0, self.contentFrame.size.width + 44.0, self.contentFrame.size.height);
        self.closedFrame = CGRectMake(-self.contentFrame.size.width-3, 88.0, self.contentFrame.size.width + 44.0, self.contentFrame.size.height);
    } else {
        self.openFrame = CGRectMake(width - self.contentFrame.size.width + 3.0 - self.tabView.frame.size.width, 88.0, self.contentFrame.size.width + 44.0, self.contentFrame.size.height);
        self.closedFrame = CGRectMake(width - self.tabView.frame.size.width + 3.0, 88.0, self.contentFrame.size.width + 44.0, self.contentFrame.size.height);
    }
    self.view.frame = self.closedFrame;
    
    if (self.onLeft) {
        self.contentView.frame = CGRectMake(0, 44, self.contentFrame.size.width, self.contentFrame.size.height);
    } else {
        self.contentView.frame = CGRectMake(self.tabView.frame.size.width, 44, self.contentFrame.size.width, self.contentFrame.size.height);
    }
    _shadowFrame = self.contentView.frame;
    if (self.onLeft) {
        self.backgroundView = [[UIView alloc] initWithFrame:CGRectMake(0.0, 44.0, self.contentFrame.size.width, self.contentFrame.size.height)];
    } else {
        self.backgroundView = [[UIView alloc] initWithFrame:CGRectMake(self.tabView.frame.size.width, 44.0, self.contentFrame.size.width, self.contentFrame.size.height)];
    }
    CGRect backgroundFrame = self.backgroundView.frame;
    backgroundFrame.size.height += 45;
    backgroundFrame.size.width += 1;
    backgroundFrame.origin.y = 0;
    self.backgroundView.frame = backgroundFrame;
    [self.view addSubview:self.backgroundView];
    [self.view sendSubviewToBack:self.backgroundView];
    self.contentViewController.view.frame = self.contentFrame;
    [self.contentView addSubview:self.contentViewController.view];
    [self addChildViewController:self.contentViewController];
    [self.contentViewController didMoveToParentViewController:self];
    [self setContentShadow];
}

- (void)setToolbarTitle:(NSString *)title withLeftButton:(UIBarButtonItem *)leftButton andRightButton:(UIBarButtonItem *)rightButton {
    NSMutableArray *buttons = [[NSMutableArray alloc] init];
    if(leftButton) {
        [buttons addObject:leftButton];
    }
    [buttons addObject:[[UIBarButtonItem alloc] initWithBarButtonSystemItem:UIBarButtonSystemItemFlexibleSpace target:nil action:nil]];
    
    UILabel *tmpLabel = [[UILabel alloc] initWithFrame:CGRectMake(0, 0, 180, 40)];
    [tmpLabel setText:title];
    [tmpLabel setBackgroundColor:[UIColor clearColor]];
    [tmpLabel setTextAlignment:NSTextAlignmentCenter];
    [tmpLabel setFont:[UIFont boldSystemFontOfSize:20.0]];
    [tmpLabel setTextColor:[UIColor darkGrayColor]];
    UIBarButtonItem *titleBar = [[UIBarButtonItem alloc] initWithCustomView:tmpLabel];
    
    
    [buttons addObject:titleBar];
    [buttons addObject:[[UIBarButtonItem alloc] initWithBarButtonSystemItem:UIBarButtonSystemItemFlexibleSpace target:nil action:nil]];
    if(rightButton) {
        [buttons addObject:rightButton];
    }
    [self.toolbar setItems:buttons];
}

- (void)setContentShadow {
    CGRect backgroundFrame = self.backgroundView.frame;
    NSLog(@"%f",backgroundFrame.size.height);
    backgroundFrame.size.height = self.view.frame.size.height+45;
    NSLog(@"%f",backgroundFrame.size.height);
    self.backgroundView.frame = backgroundFrame;
    self.backgroundView.layer.borderColor = [[UIColor darkGrayColor] CGColor];
    self.backgroundView.layer.borderWidth = 2;
    [self.backgroundView.layer setShadowColor:[UIColor blackColor].CGColor];
    [self.backgroundView.layer setShadowOpacity:0.65];
    [self.backgroundView.layer setShadowRadius:3.0];
    if (self.onLeft) {
        [self.backgroundView.layer setShadowPath:[UIBezierPath bezierPathWithRect:self.backgroundView.frame].CGPath];
    } else {
        [self.backgroundView.layer setShadowPath:[UIBezierPath bezierPathWithRect:CGRectMake(0.0, 0.0, self.backgroundView.frame.size.width, self.backgroundView.frame.size.height)].CGPath];
    }
    
}

- (void)setupTabImage:(NSString *)imagePath atVerticalPosition:(float)verticalPercentage {
    _tabVerticalPercentage = verticalPercentage;
    _tabImageName = imagePath;
}

- (void)viewWillAppear:(BOOL)animated
{
    [super viewWillAppear:animated];
    self.view.frame = self.closedFrame;
    DLog(@"XXX %g",self.contentFrame.size.width);
}

- (void)setupInteraction
{
    UITapGestureRecognizer *tap = [[UITapGestureRecognizer alloc] initWithTarget:self action:@selector(toggleDrawer:)];
    [self.tabView addGestureRecognizer:tap];
}

- (void)toggleDrawer:(id)sender
{
    [UIView beginAnimations:nil context:nil];
    [UIView setAnimationDuration:0.2];
    [UIView setAnimationCurve:UIViewAnimationCurveEaseOut];
    
    if (_isOpen) {
        self.view.frame = self.closedFrame;
        [self.contentViewController drawerWillHide];
    } else {
        self.view.frame = self.openFrame;
        [self.view.superview bringSubviewToFront:self.view];
        [self.contentViewController drawerWillShow];
    }
    
    self.view.frame = _isOpen ? self.closedFrame : self.openFrame;
    _isOpen = !_isOpen;
    
    [UIView commitAnimations];
}

- (void)setContentWidth:(float)width {
    _contentWidth = width;
}

//- (void)willRotateToInterfaceOrientation:(UIInterfaceOrientation)toInterfaceOrientation duration:(NSTimeInterval)duration
//{
//    if (UIDeviceOrientationIsLandscape(toInterfaceOrientation)) {
//        _shadowFrame = CGRectMake(0.0, 0.0, _contentWidth, self.view.frame.size.width - 140.0);
//    } else {
//        _shadowFrame = CGRectMake(0.0, 0.0, _contentWidth, self.view.frame.size.height - 140.0);
//    }
//}

- (void)didRotateFromInterfaceOrientation:(UIInterfaceOrientation)fromInterfaceOrientation
{
    if (self.onLeft) {
        self.closedFrame = CGRectMake(self.closedFrame.origin.x, self.closedFrame.origin.y, self.view.frame.size.width, self.view.frame.size.height);
        self.openFrame = CGRectMake(self.openFrame.origin.x, self.openFrame.origin.y, self.view.frame.size.width, self.view.frame.size.height);
    } else {
        if (UIDeviceOrientationIsLandscape([UIApplication sharedApplication].statusBarOrientation)) {
            self.closedFrame = CGRectMake(1024.0 - self.tabView.frame.size.width + 3.0, self.closedFrame.origin.y, self.view.frame.size.width, self.view.frame.size.height);
            self.openFrame = CGRectMake(1024.0 - self.tabView.frame.size.width - self.contentFrame.size.width, self.openFrame.origin.y, self.view.frame.size.width, self.view.frame.size.height);
        } else {
            self.closedFrame = CGRectMake(768.0 - self.tabView.frame.size.width + 3.0, self.closedFrame.origin.y, self.view.frame.size.width, self.view.frame.size.height);
            self.openFrame = CGRectMake(768.0 - self.tabView.frame.size.width - self.contentFrame.size.width, self.openFrame.origin.y, self.view.frame.size.width, self.view.frame.size.height);
        }
    }
    [self setContentShadow];
}

- (void)didReceiveMemoryWarning
{
    [super didReceiveMemoryWarning];
    // Dispose of any resources that can be recreated.
}

@end
