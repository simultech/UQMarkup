//
//  TermsOfUseViewController.m
//  Markup2
//

#import "TermsOfUseViewController.h"
#import "MarkupAPIController.h"

@interface TermsOfUseViewController ()

@end

@implementation TermsOfUseViewController

- (id)initWithNibName:(NSString *)nibNameOrNil bundle:(NSBundle *)nibBundleOrNil
{
    self = [super initWithNibName:nibNameOrNil bundle:nibBundleOrNil];
    if (self) {
        // Custom initialization
    }
    return self;
}

- (void)viewDidLoad
{
    [super viewDidLoad];
	// Do any additional setup after loading the view.
    [self.webView loadRequest:[NSURLRequest requestWithURL:[NSURL URLWithString:@"http://markup.sbms.uq.edu.au/ipadtermsofuse.html"]]];
}

- (IBAction)agree:(id)sender {
    [[MarkupAPIController sharedApi] agreeToTermsWithSucess:^{
        [self.delegate didLogin];
        [self dismissViewControllerAnimated:YES completion:NULL];
    } andFailure:^(NSError *error) {
        UIAlertView *message = [[UIAlertView alloc] initWithTitle:@"Something went wrong, Ooops"
                                                          message:@"Please contact us, error code 73"
                                                         delegate:nil
                                                cancelButtonTitle:@"OK"
                                                otherButtonTitles:nil];
        
        [message show];
        [self.navigationController popViewControllerAnimated:YES];
    }];
}

- (void)didReceiveMemoryWarning
{
    [super didReceiveMemoryWarning];
    // Dispose of any resources that can be recreated.
}

@end
