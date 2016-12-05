//
//  LoginViewController.m
//  Markup2
//

#import "LoginViewController.h"
#import "MarkupAPIController.h"
#import "UIApplication+PRPNetworkActivity.h"
#import <QuartzCore/QuartzCore.h>
#import "TermsOfUseViewController.h"
#import "UINavigationController+DismissKeyboard.h"

@interface LoginViewController () <UITextFieldDelegate>
@property (weak, nonatomic) IBOutlet UITextField *username;
@property (weak, nonatomic) IBOutlet UITextField *password;
@property (weak, nonatomic) IBOutlet UIPickerView *institution;

@property (weak, nonatomic) IBOutlet UILabel *errorLabel;
@property (weak, nonatomic) IBOutlet UIView *errorView;

- (IBAction)login:(id)sender;
@end

@implementation LoginViewController {
    UIResponder *_currentResponder;
}

- (id)initWithNibName:(NSString *)nibNameOrNil bundle:(NSBundle *)nibBundleOrNil
{
    self = [super initWithNibName:nibNameOrNil bundle:nibBundleOrNil];
    if (self) {
        // Custom initialization
    }
    return self;
}

- (void)viewWillAppear:(BOOL)animated
{
    [super viewWillAppear:animated];
    [self.username becomeFirstResponder];
}

- (void)viewDidLoad
{
    [super viewDidLoad];
    [[MarkupAPIController sharedApi] getMarkupLocationsWithSuccess:^(NSArray *locations) {
        NSLog(@"STUFF");
        self.institutions = locations;
        [self.institution reloadAllComponents];
    } andFailure:^(NSError *error) {
        NSLog(@"ERROR");
    }];
    //self.institutions = @[@"University of Queensland",@"Auckland University",@"University of News South Wales",@"University of News South Wales",@"University of News South Wales",@"University of News South Wales"];
	// Do any additional setup after loading the view.
}

- (void)didReceiveMemoryWarning
{
    [super didReceiveMemoryWarning];
    // Dispose of any resources that can be recreated.
}

- (IBAction)login:(id)sender {
    [[UIApplication sharedApplication] prp_pushNetworkActivity];
    [[MarkupAPIController sharedApi] loginWithUsername:self.username.text andPassword:self.password.text withSucess:^{
        [[UIApplication sharedApplication] prp_popNetworkActivity];
        [self.errorView setHidden:YES];
        [[MarkupAPIController sharedApi] checkHasAgreedToTermsWithSucess:^(NSString *hasAgreed){
            if([hasAgreed isEqualToString:@"true"]) {
                [self.delegate didLogin];
                [self dismissViewControllerAnimated:YES completion:NULL];
            } else {
                [self.username resignFirstResponder];
                [self.password resignFirstResponder];
                [self performSegueWithIdentifier:@"ShowTermsOfUseSegue" sender:self];
            }
            NSLog(@"User has agreed: %@",hasAgreed);
        } andFailure:^(NSError *error) {
            NSLog(@"COULDNT AGREE OR SOMETHING");
        }];
    } andFailure:^(NSError *error) {
        [[UIApplication sharedApplication] prp_popNetworkActivity];
        DLog(@"%@",error);
        self.errorLabel.text = @"Couldn't log in. Please check your username and password.";
        self.errorView.layer.cornerRadius = 5;
        [self.errorView setHidden:NO];
        [self.errorView setAlpha:0];
        [UIView beginAnimations:nil context:NULL];
        [UIView setAnimationDuration:0.5];
        [self.errorView setAlpha:1.0];
        [UIView commitAnimations];
    }];
}

- (void)prepareForSegue:(UIStoryboardSegue *)segue sender:(id)sender {
    if ([[segue identifier] isEqualToString:@"ShowTermsOfUseSegue"])
    {
        // Get reference to the destination view controller
        TermsOfUseViewController *vc = [segue destinationViewController];
        
        // Pass any objects to the view controller here, like...
        [vc setDelegate:self.delegate];
    }
    [self.username resignFirstResponder];
    [self.password resignFirstResponder];
}

#pragma mark UITextFieldDelegate methods
- (void)textFieldDidBeginEditing:(UITextField *)textField
{
    [textField becomeFirstResponder];
}

- (void)textFieldDidEndEditing:(UITextField *)textField
{
    [textField resignFirstResponder];
}

- (BOOL)textFieldShouldReturn:(UITextField *)textField
{
    if ([textField isEqual:self.username]) {
        [self.password becomeFirstResponder];
    } else {
        [self.password resignFirstResponder];
        [self login:self];
    }
    
    return NO;
}

#pragma mark UIPickerViewDataSource methods

// returns the number of 'columns' to display.
- (NSInteger)numberOfComponentsInPickerView:(UIPickerView *)pickerView {
    return 1.0;
}

// returns the # of rows in each component..
- (NSInteger)pickerView:(UIPickerView *)pickerView numberOfRowsInComponent:(NSInteger)component {
    return [self.institutions count];
}

- (UIView *)pickerView:(UIPickerView *)pickerView viewForRow:(NSInteger)row forComponent:(NSInteger)component reusingView:(UIView *)view{
    UILabel* tView = (UILabel*)view;
    if (!tView){
        tView = [[UILabel alloc] init];
    }
    NSDictionary *institution = [self.institutions objectAtIndex:row];
    if(institution) {
        [tView setText:[institution objectForKey:@"name"]];
    }
    return tView;
}

- (void)pickerView:(UIPickerView *)pickerView didSelectRow:(NSInteger)row inComponent:(NSInteger)component {
    NSDictionary *institution = [self.institutions objectAtIndex:row];
    if(institution) {
        [MarkupAPIController setBaseURL:[institution objectForKey:@"url"]];
    }
    NSLog(@"TEST");
}


@end
