//
//  AnnotationLibraryViewController.m
//  Markup2
//

#import "AnnotationLibraryViewController.h"
#import "LibraryAnnotation.h"
#import "AnnotationLibraryCell.h"
#import "AudioAnnotationView.h"
#import <QuartzCore/QuartzCore.h>

#define kThumbViewFrame CGRectMake(0.0,0.0,280.0,100.0)

@interface AnnotationLibraryViewController () <NSFetchedResultsControllerDelegate>

@property (nonatomic, strong) NSFetchedResultsController *fetchedResultsController;
@property (nonatomic, strong) NSMutableDictionary *annotationThumbs;
@property BOOL userDrivenDataModelChange;
@property BOOL hasResortedQuickBugFix;
@end

@implementation AnnotationLibraryViewController

- (id)initWithCoder:(NSCoder *)aDecoder
{
    self = [super initWithCoder:aDecoder];
    if (self) {
        // Custom initialization
        self.annotationThumbs = [[NSMutableDictionary alloc] init];
        
    }
    return self;
}

- (void)viewDidLoad
{
    [super viewDidLoad];

    // Uncomment the following line to preserve selection between presentations.
    // self.clearsSelectionOnViewWillAppear = NO;
 
    // Uncomment the following line to display an Edit button in the navigation bar for this view controller.
    // self.navigationItem.rightBarButtonItem = self.editButtonItem;
}

- (void)didReceiveMemoryWarning
{
    [super didReceiveMemoryWarning];
    [self.annotationThumbs removeAllObjects];
    // Dispose of any resources that can be recreated.
}

#pragma mark - Table view data source

- (NSInteger)numberOfSectionsInTableView:(UITableView *)tableView
{
    // Return the number of sections.
    return self.fetchedResultsController.sections.count;
}

- (NSInteger)tableView:(UITableView *)tableView numberOfRowsInSection:(NSInteger)section
{
    // Return the number of rows in the section.
    id<NSFetchedResultsSectionInfo> sectionInfo = [[self.fetchedResultsController sections] objectAtIndex:section];
    return [sectionInfo numberOfObjects];
}

- (UITableViewCell *)tableView:(UITableView *)tableView cellForRowAtIndexPath:(NSIndexPath *)indexPath
{
    static NSString *CellIdentifier = @"LibraryCellIdentifier";
    AnnotationLibraryCell *cell = (AnnotationLibraryCell *)[tableView dequeueReusableCellWithIdentifier:CellIdentifier forIndexPath:indexPath];
    
    // Configure the cell...
    LibraryAnnotation *libAnn = [self.fetchedResultsController objectAtIndexPath:indexPath];
    for (UIView *subview in cell.thumbnailView.subviews) {
        [subview removeFromSuperview];
    }
    
    __block UIView *annThumbView = [self.annotationThumbs objectForKey:libAnn.orderIndex];
    annThumbView = nil;
    if (!annThumbView) {
        dispatch_async(dispatch_get_main_queue(), ^{
            annThumbView = [self thumbViewForAnnotation:libAnn];
            [cell.thumbnailView addSubview:annThumbView];
        });
    } else {
        [cell.thumbnailView addSubview:annThumbView];
    }
    
    
    return cell;
}
                        
- (UIView *)thumbViewForAnnotation:(LibraryAnnotation *)libAnn
{
    
    id thumb;
    if ([libAnn.annotationType isEqualToString:@"Text"]) {
        UITextView *tv = [[UITextView alloc] initWithFrame:kThumbViewFrame];
        tv.editable = NO;
        tv.textAlignment = NSTextAlignmentCenter;
        tv.font = [UIFont systemFontOfSize:14.0];
        tv.userInteractionEnabled = NO;
        tv.text = libAnn.title;
        tv.textColor = [UIColor colorWithHexString:libAnn.colour];
        thumb = tv;
        [self.annotationThumbs setObject:tv forKey:libAnn.orderIndex];
    } else if ([libAnn.annotationType isEqualToString:@"Recording"]) {
        CGRect audioFrame = CGRectMake((kThumbViewFrame.size.width / 2.0) - 22.0, (kThumbViewFrame.size.height / 2.0) - 22.0, 44.0, 44.0);
        AudioAnnotationView *av = [[AudioAnnotationView alloc] initWithFrame:audioFrame andAudioAnnotation:(Annotation *)libAnn];
        thumb = av;
        [self.annotationThumbs setObject:av forKey:libAnn.orderIndex];
    } else {
        UIGraphicsBeginImageContextWithOptions(kThumbViewFrame.size, YES, 0.0);
        CGContextRef ctx = UIGraphicsGetCurrentContext();
        CGContextSetFillColorWithColor(ctx, [UIColor whiteColor].CGColor);
        CGContextFillRect(ctx, kThumbViewFrame);
        UIImage *fullImage = [UIImage imageWithContentsOfFile:[libAnn localFilePath]];
        CGRect imageRect = CGRectMake(0.0, 0.0, fullImage.size.width, fullImage.size.height);
        CGFloat imageScale = kThumbViewFrame.size.height / fullImage.size.height;
        imageRect.size = CGSizeMake(imageScale * imageRect.size.width, imageScale * imageRect.size.height);
        imageRect.origin = CGPointMake((kThumbViewFrame.size.width / 2.0) - (imageRect.size.width / 2.0), 0.0);
        CGContextSaveGState(ctx);
        CGContextTranslateCTM(ctx, 0.0, kThumbViewFrame.size.height);
        CGContextScaleCTM(ctx, 1.0, -1.0);
        CGContextDrawImage(ctx, imageRect, fullImage.CGImage);
        
        CGContextRestoreGState(ctx);
        
        UIImage *thumbImage = UIGraphicsGetImageFromCurrentImageContext();
        UIGraphicsEndImageContext();
        
        UIImageView *thumbView = [[UIImageView alloc] initWithFrame:kThumbViewFrame];
        [thumbView setContentMode:UIViewContentModeCenter];
        [thumbView setImage:thumbImage];
        
        [self.annotationThumbs setObject:thumbView forKey:libAnn.orderIndex];
        thumb = thumbView;
    }
    
    return thumb;
}

// Override to support conditional editing of the table view.
- (BOOL)tableView:(UITableView *)tableView canEditRowAtIndexPath:(NSIndexPath *)indexPath
{
    // Return NO if you do not want the specified item to be editable.
    return YES;
}

// Override to support editing the table view.
- (void)tableView:(UITableView *)tableView commitEditingStyle:(UITableViewCellEditingStyle)editingStyle forRowAtIndexPath:(NSIndexPath *)indexPath
{
    if (editingStyle == UITableViewCellEditingStyleDelete) {
        // Delete the row from the data source
        NSLog(@"DELETED LIBRARY ANNOTATION %@",self.pdfViewController);
        LibraryAnnotation *ann = [self.fetchedResultsController objectAtIndexPath:indexPath];
        [[NSNotificationCenter defaultCenter] postNotificationName:@"Create_Log" object:@{@"type":@"Library",@"action":@"Delete",@"value":ann.annotationType}];
        [ann deleteEntity];
        [[NSManagedObjectContext defaultContext] save];
    }
}

// Override to support rearranging the table view.
- (void)tableView:(UITableView *)tableView moveRowAtIndexPath:(NSIndexPath *)fromIndexPath toIndexPath:(NSIndexPath *)toIndexPath
{
    self.userDrivenDataModelChange = YES;
    
    self.hasResortedQuickBugFix = YES;
    
    NSUInteger fromIndex = fromIndexPath.row;
    NSUInteger toIndex = toIndexPath.row;
    
    if (fromIndex == toIndex) {
        return;
    }
    
    LibraryAnnotation *affectedObject = [self.fetchedResultsController.fetchedObjects objectAtIndex:fromIndex];
    affectedObject.orderIndex = @(toIndex);
    
    NSUInteger start, end;
    int delta;
    
    if (fromIndex < toIndex) {
        // move was down, need to shift up
        delta = -1;
        start = fromIndex + 1;
        end = toIndex;
    } else { // fromIndex > toIndex
        // move was up, need to shift down
        delta = 1;
        start = toIndex;
        end = fromIndex - 1;
    }
    
    for (NSUInteger i = start; i <= end; i++) {
        LibraryAnnotation *otherObject = [self.fetchedResultsController.fetchedObjects objectAtIndex:i];
        int newOrder = [otherObject.orderIndex integerValue];
        otherObject.orderIndex = @(newOrder + delta);
    }
    [[NSManagedObjectContext defaultContext] save];
    self.fetchedResultsController = nil;
    
    self.userDrivenDataModelChange = NO;
}

// Override to support conditional rearranging of the table view.
- (BOOL)tableView:(UITableView *)tableView canMoveRowAtIndexPath:(NSIndexPath *)indexPath
{
    // Return NO if you do not want the item to be re-orderable.
    return YES;
}

#pragma mark - Table view delegate

- (void)tableView:(UITableView *)tableView didSelectRowAtIndexPath:(NSIndexPath *)indexPath
{
    if(self.hasResortedQuickBugFix) {
        self.hasResortedQuickBugFix = NO;
        [self.tableView reloadData];
    }
    // Navigation logic may go here. Create and push another view controller.
    LibraryAnnotation *libAnnot = [self.fetchedResultsController objectAtIndexPath:indexPath];
    [[NSNotificationCenter defaultCenter] postNotificationName:@"libraryAnnotationSelected" object:libAnnot];
    [tableView deselectRowAtIndexPath:indexPath animated:YES];
}

- (NSFetchedResultsController *)fetchedResultsController
{
    if (!_fetchedResultsController) {
        _fetchedResultsController = [LibraryAnnotation fetchAllSortedBy:@"orderIndex" ascending:YES withPredicate:nil groupBy:nil delegate:self];
        _fetchedResultsController.delegate = self;
    }
    
    return _fetchedResultsController;
}

#pragma mark - NSFetchedResultsControllerDelegate methods
- (void)controllerWillChangeContent:(NSFetchedResultsController *)controller
{
    if (self.userDrivenDataModelChange) return;
    [self.tableView beginUpdates];
}

- (void)controller:(NSFetchedResultsController *)controller didChangeObject:(id)anObject atIndexPath:(NSIndexPath *)indexPath forChangeType:(NSFetchedResultsChangeType)type newIndexPath:(NSIndexPath *)newIndexPath
{
    if (self.userDrivenDataModelChange) return;
    switch (type) {
        case NSFetchedResultsChangeDelete:
            [self.tableView deleteRowsAtIndexPaths:@[indexPath] withRowAnimation:UITableViewRowAnimationRight];
            break;
        case NSFetchedResultsChangeMove:
            [self.tableView moveRowAtIndexPath:indexPath toIndexPath:newIndexPath];
            break;
        case NSFetchedResultsChangeInsert:
            [self.tableView insertRowsAtIndexPaths:@[newIndexPath] withRowAnimation:UITableViewRowAnimationFade];
        default:
            break;
    }
}

- (void)controllerDidChangeContent:(NSFetchedResultsController *)controller
{
    if (self.userDrivenDataModelChange) return;
    [self.tableView endUpdates];
}

#pragma mark Drawer methods
- (void)drawerWillHide
{
    
}

- (void)drawerWillShow
{
    
}

@end
