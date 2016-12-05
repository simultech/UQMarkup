//
//  PalmProtectViewController.m
//  UQMySignment
//
//  Created by simultech on 12/05/11.
//  Copyright 2011 __MyCompanyName__. All rights reserved.
//

#import "PalmProtectViewController.h"

@implementation PalmProtectViewController

static id _pptype;

@synthesize leftButton,noButton,rightButton;

+ (NSString *) palmProtectType {
    return _pptype;
}

+ (void) setPalmProtectType:(NSString *)newType {
    _pptype = newType;
}

- (id)initWithNibName:(NSString *)nibNameOrNil bundle:(NSBundle *)nibBundleOrNil
{
    self = [super initWithNibName:nibNameOrNil bundle:nibBundleOrNil];
    if (self) {
        [PalmProtectViewController setPalmProtectType:@"Right"];
        // Custom initialization
        self.leftButton = [UIButton buttonWithType:UIButtonTypeCustom];
        [self.leftButton setFrame:CGRectMake(5, 5, 90, 30)];
        [self.leftButton setBackgroundImage:[UIImage imageNamed:@"palm_button"] forState:UIControlStateNormal];
        [self.leftButton setBackgroundImage:[UIImage imageNamed:@"palm_button_selected"] forState:UIControlStateSelected];
        [self.leftButton setTitleColor:[UIColor darkGrayColor] forState:UIControlStateNormal];
        [[self.leftButton layer] setCornerRadius:4.0];
        [[self.leftButton layer] setMasksToBounds:YES];
        [[self.leftButton layer] setBorderWidth:1.0f];
        [self.leftButton setTitle:@"Left" forState:UIControlStateNormal];
        [self.leftButton addTarget:self action:@selector(buttonClicked:) forControlEvents:UIControlEventTouchUpInside];
        [self.view addSubview:self.leftButton];
        self.noButton = [UIButton buttonWithType:UIButtonTypeCustom];
        [self.noButton setFrame:CGRectMake(105, 5, 90, 30)];
        [self.noButton setBackgroundImage:[UIImage imageNamed:@"palm_button"] forState:UIControlStateNormal];
        [self.noButton setBackgroundImage:[UIImage imageNamed:@"palm_button_selected"] forState:UIControlStateSelected];
        [self.noButton setTitleColor:[UIColor darkGrayColor] forState:UIControlStateNormal];
        [[self.noButton layer] setCornerRadius:4.0];
        [[self.noButton layer] setMasksToBounds:YES];
        [[self.noButton layer] setBorderWidth:1.0f];
        [self.noButton setTitle:@"Disabled" forState:UIControlStateNormal];
        [self.noButton addTarget:self action:@selector(buttonClicked:) forControlEvents:UIControlEventTouchUpInside];
        [self.view addSubview:self.noButton];
        self.rightButton = [UIButton buttonWithType:UIButtonTypeCustom];
        [self.rightButton setFrame:CGRectMake(205, 5, 90, 30)];
        [self.rightButton setBackgroundImage:[UIImage imageNamed:@"palm_button"] forState:UIControlStateNormal];
        [self.rightButton setBackgroundImage:[UIImage imageNamed:@"palm_button_selected"] forState:UIControlStateSelected];
        [self.rightButton setTitleColor:[UIColor darkGrayColor] forState:UIControlStateNormal];
        [self.rightButton setBackgroundColor:[UIColor blueColor]];
        [[self.rightButton layer] setCornerRadius:4.0];
        [[self.rightButton layer] setMasksToBounds:YES];
        [[self.rightButton layer] setBorderWidth:1.0f];
        [self.rightButton setTitle:@"Right" forState:UIControlStateNormal];
        [self.rightButton addTarget:self action:@selector(buttonClicked:) forControlEvents:UIControlEventTouchUpInside];
        [self.view addSubview:self.rightButton];
        [self getPalmType];
    }
    return self;
}

- (void)getPalmType {
    [self.leftButton setSelected:NO];
    [self.noButton setSelected:NO];
    [self.rightButton setSelected:NO];
    if ([[PalmProtectViewController palmProtectType] isEqualToString:@"Left"]) {
        [self.leftButton setSelected:YES];
    }
    if ([[PalmProtectViewController palmProtectType] isEqualToString:@"Disabled"]) {
        [self.noButton setSelected:YES];
    }
    if ([[PalmProtectViewController palmProtectType] isEqualToString:@"Right"]) {
        [self.rightButton setSelected:YES];
    }
}

- (void)buttonClicked:(id)sender {
    [PalmProtectViewController setPalmProtectType:((UIButton *)sender).titleLabel.text];
    [self getPalmType];
}

- (void)viewDidLoad {
    [self getPalmType];
}

- (void)didReceiveMemoryWarning
{
    // Releases the view if it doesn't have a superview.
    [super didReceiveMemoryWarning];
    
    // Release any cached data, images, etc that aren't in use.
}

#pragma mark - View lifecycle

/*
// Implement loadView to create a view hierarchy programmatically, without using a nib.
- (void)loadView
{
}
*/

/*
// Implement viewDidLoad to do additional setup after loading the view, typically from a nib.
- (void)viewDidLoad
{
    [super viewDidLoad];
}
*/

- (void)viewDidUnload
{
    [super viewDidUnload];
    // Release any retained subviews of the main view.
    // e.g. self.myOutlet = nil;
}

- (BOOL)shouldAutorotateToInterfaceOrientation:(UIInterfaceOrientation)interfaceOrientation
{
    // Return YES for supported orientations
	return YES;
}

@end
