//
//  SubmissionCell.h
//  SectionCollectionViewTest
//

#import <UIKit/UIKit.h>
#import "SubmissionDownload.h"

@interface SubmissionCell : UICollectionViewCell

@property (weak, nonatomic) IBOutlet UIImageView *documentThumb;
@property (weak, nonatomic) IBOutlet UILabel *documentTitleLabel;
@property (weak, nonatomic) IBOutlet UILabel *documentStudentNoLabel;
@property (nonatomic, weak) SubmissionDownload *download;
@property (weak, nonatomic) IBOutlet UIProgressView *downloadProgress;
@property (weak, nonatomic) IBOutlet UIImageView *markedBox;
@property (weak, nonatomic) IBOutlet UIImageView *changedBox;
@property (nonatomic, strong) UIView *borderView;
@property (nonatomic, strong) NSSet *annotations;
@property (nonatomic, strong) UIView *highlightView;

- (void)setTitlePage:(CGPDFPageRef)page;
@end
