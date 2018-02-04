//
//  PublishController.m
//  Markup2
//

#import "PublishController.h"
#import "Submission.h"
#import "Mark.h"
#import "Annotation.h"
#import "AudioAnnotationView.h"
#import "Objective-Zip/ZipFile.h"
#import "Objective-Zip/ZipWriteStream.h"
#import "Objective-Zip/ZipException.h"
#import "Log.h"

@implementation PublishController

#pragma mark - Public methods
- (NSString *)prepareSubmission:(NSInteger)submissionId destinationFileSize:(int64_t *)outFileSize withError:(NSError *)outError
{
    // Create JSON data for the specified annotations
    Submission *sub = [Submission findFirstByAttribute:@"submissionId" withValue:@(submissionId)];
    NSError *annotsError;
    NSData *annotsJSON = [self createAnnotationJSONForSubmission:sub withError:annotsError];
    NSError *marksError;
    NSData *marksJSON = [self createMarksJSONForSubmission:sub withError:marksError];
    NSError *logsError;
    NSData *logsJSON = [self createLogsJSONForSubmission:sub withError:logsError];
    
    NSString *publishDir = [NSTemporaryDirectory() stringByAppendingPathComponent:[NSString stringWithFormat:@"%@",sub.submissionId]];
    
    // Zip the submission directory
    NSString *zipFilePath = [publishDir stringByAppendingPathExtension:@".zip"];
    ZipFile *zip = [[ZipFile alloc] initWithFileName:zipFilePath mode:ZipFileModeCreate];
    @try {
        ZipWriteStream *annotsJSONStream = [zip writeFileInZipWithName:@"annots/annots.json" compressionLevel:ZipCompressionLevelDefault];
        [annotsJSONStream writeData:annotsJSON];
        [annotsJSONStream finishedWriting];
        ZipWriteStream *marksJSONStream = [zip writeFileInZipWithName:@"marks.json" compressionLevel:ZipCompressionLevelBest];
        [marksJSONStream writeData:marksJSON];
        [marksJSONStream finishedWriting];
        ZipWriteStream *logsJSONStream = [zip writeFileInZipWithName:@"logs.json" compressionLevel:ZipCompressionLevelBest];
        [logsJSONStream writeData:logsJSON];
        [logsJSONStream finishedWriting];
        
        for (Annotation *annot in sub.annotations) {
            if([annot.annotationType isEqualToString:@"Text"]) {
                continue;
            }
            NSString *annotPath = [NSString stringWithFormat:@"annots/%@", annot.localFileName];
            ZipWriteStream *annotStream = [zip writeFileInZipWithName:annotPath compressionLevel:ZipCompressionLevelNone];
            NSData *annotData = [NSData dataWithContentsOfFile:[annot localFilePath]];
            [annotStream writeData:annotData];
            [annotStream finishedWriting];
        }
        
        ZipWriteStream *pdfStream = [zip writeFileInZipWithName:[[sub localPDFPath] lastPathComponent] compressionLevel:ZipCompressionLevelDefault];
        [self bakeAnnotationsToDocumentForSubmission:sub];
        NSData *pdfData = [NSData dataWithContentsOfFile:[sub localBakedPDFPath]];
        [pdfStream writeData:pdfData];
        [pdfStream finishedWriting];
        
        
    }
    @catch (ZipException *ze) {
        DLog(@"%@", ze);
    }
    @finally {
        [zip close];
    }
    
    // Get file size for new zip
    NSError *fatError;
    NSDictionary *fileAttributes = [[NSFileManager defaultManager] attributesOfItemAtPath:zipFilePath error:&fatError];
    if (fatError) {
        DLog(@"%@", fatError);
    }
    int64_t filesize = [fileAttributes fileSize];
    *outFileSize = filesize;
    
    return zipFilePath;
}

#pragma mark - JSON serialisation of annotation data

- (NSData *)createAnnotationJSONForSubmission:(Submission *)submission withError:(NSError *)outError
{
    NSMutableArray *annots = [[NSMutableArray alloc] initWithCapacity:submission.annotations.count];
    for (Annotation *annot in submission.annotations) {
        BOOL save = YES;
        
        if([[NSString stringWithFormat:@"%@",annot.xPos] isEqualToString:@"inf"] || [[NSString stringWithFormat:@"%@",annot.yPos] isEqualToString:@"inf"]) {
            save = NO;
        }
        if(save) {
            NSLog(@"SAVING ANNOT");
            [annots addObject:[annot toDict]];
        }
    }
    NSData *annotsJSON = [NSJSONSerialization dataWithJSONObject:annots options:0 error:&outError];
    return annotsJSON;
}

- (NSData *)createMarksJSONForSubmission:(Submission *)submission withError:(NSError *)outError
{
    NSMutableArray *marks = [[NSMutableArray alloc] initWithCapacity:submission.marks.count];
    for (Mark *mark in submission.marks) {
        [marks addObject:[mark toDict]];
    }
    NSDictionary *marksDict = @{@"time_spent_marking" : submission.timeSpentMarking, @"marks": marks};
    
    NSData *marksJSON = [NSJSONSerialization dataWithJSONObject:marksDict options:0 error:&outError];
    return marksJSON;
}

- (NSData *)createLogsJSONForSubmission:(Submission *)submission withError:(NSError *)outError
{
    NSMutableArray *logs = [[NSMutableArray alloc] initWithCapacity:submission.logs.count];
    for (Log *log in submission.logs) {
        [logs addObject:[log toDict]];
    }
    NSDictionary *logsDict = @{@"logs": logs};
    
    NSData *logsJSON = [NSJSONSerialization dataWithJSONObject:logsDict options:0 error:&outError];
    return logsJSON;
}


#pragma mark Annotation baking

- (void)bakeAnnotationsToDocumentForSubmission:(Submission *)submission
{
    NSData *pdfData = [NSData dataWithContentsOfFile:[submission localPDFPath]];
    CGDataProviderRef pdfDataRef = CGDataProviderCreateWithCFData((__bridge CFDataRef)(pdfData));
    CGPDFDocumentRef pdf = CGPDFDocumentCreateWithProvider(pdfDataRef);
    
    int numPages = CGPDFDocumentGetNumberOfPages(pdf);
    UIGraphicsBeginPDFContextToFile([submission localBakedPDFPath], CGRectZero, nil);
    CGContextRef pdfContext = UIGraphicsGetCurrentContext();
    
    for (int i = 1; i <= numPages; i++) {
        CGPDFPageRef page = CGPDFDocumentGetPage(pdf, i);
        CGRect pageRect = CGPDFPageGetBoxRect(page, kCGPDFMediaBox);
        UIGraphicsBeginPDFPageWithInfo(pageRect, nil);
        
        CGContextTranslateCTM(pdfContext, 0.0, pageRect.size.height);
        CGContextScaleCTM(pdfContext, 1.0, -1.0);
        CGContextDrawPDFPage(pdfContext, page);
        
        // Flip back after drawing page? Not sure, need to check output
        [self bakeAnnotationsToContext:pdfContext forPage:i pageRect:pageRect inSubmission:submission];
    }
    
    UIGraphicsEndPDFContext();
    CGPDFDocumentRelease(pdf);
    CGDataProviderRelease(pdfDataRef);
}

- (void)bakeAnnotationsToContext:(CGContextRef)pdfContext forPage:(int)pageNum pageRect:(CGRect)pageRect inSubmission:(Submission *)sub
{
    NSPredicate *imageAnnsPred = [NSPredicate predicateWithFormat:@"pageNumber == %d AND (annotationType == %@ OR annotationType == %@)", pageNum, @"Freehand", @"Highlight"];
    NSPredicate *textAnnsPred = [NSPredicate predicateWithFormat:@"pageNumber == %d AND annotationType == %@", pageNum, @"Text"];
//    NSPredicate *audioAnnsPred = [NSPredicate predicateWithFormat:@"pageNumber == %d AND annotationType == %@", pageNum, @"Recording"];
    
    NSSet *imageAnns = [sub.annotations filteredSetUsingPredicate:imageAnnsPred];
    NSSet *textAnns = [sub.annotations filteredSetUsingPredicate:textAnnsPred];
//    NSSet *audioAnns = [sub.annotations filteredSetUsingPredicate:audioAnnsPred];
    
    CGContextTranslateCTM(pdfContext, 0.0, pageRect.size.height);
    CGContextScaleCTM(pdfContext, 1.0, -1.0);
    [self bakeImageAnnotations:imageAnns toContext:pdfContext inPageRect:pageRect];
    [self bakeTextAnnotations:textAnns toContext:pdfContext inPageRect:pageRect];
//    [self bakeAudioAnnotations:audioAnns toContext:pdfContext inPageRect:pageRect];
}

- (void)bakeAudioAnnotations:(NSSet *)audioAnnotations toContext:(CGContextRef)pdfContext inPageRect:(CGRect)pageRect
{
    for (Annotation *audioAnnot in audioAnnotations) {
        CGFloat x = audioAnnot.xPosValue * pageRect.size.width;
        CGFloat y = audioAnnot.yPosValue * pageRect.size.height;
        CGFloat width = 30.0;
        CGFloat height = 30.0;
        CGRect annotRect = CGRectMake(x, y, width, height);
        CGRect labelRect = CGRectMake(x - 35.0, y + 32.0, 100.0, 16.0);
        
        AudioAnnotationView *annotView = [[AudioAnnotationView alloc] initWithFrame:annotRect andAudioAnnotation:audioAnnot];
        [annotView.badge.image drawInRect:annotRect];
        CGContextSetFillColorWithColor(pdfContext, [UIColor blackColor].CGColor);
        //[audioAnnot.title drawInRect:labelRect withFont:[UIFont systemFontOfSize:11.0]];
        NSDictionary *dict = @{ NSFontAttributeName: [UIFont systemFontOfSize:11.0]};
        [audioAnnot.title drawInRect:labelRect withAttributes:dict];
    }
}

- (void)bakeImageAnnotations:(NSSet *)imageAnnotations toContext:(CGContextRef)pdfContext inPageRect:(CGRect)pageRect
{
    for (Annotation *imageAnnot in imageAnnotations) {
        UIImage *annotImage = [UIImage imageWithContentsOfFile:[imageAnnot localFilePath]];
        CGFloat x = imageAnnot.xPosValue * pageRect.size.width;
        CGFloat y = imageAnnot.yPosValue * pageRect.size.height;
        CGFloat width = imageAnnot.widthValue * pageRect.size.width;
        CGFloat height = imageAnnot.heightValue * pageRect.size.height;
        CGRect annotRect = CGRectMake(x, y, width, height);
        
        [annotImage drawInRect:annotRect];
    }
}

- (void)bakeTextAnnotations:(NSSet *)textAnnotations toContext:(CGContextRef)pdfContext inPageRect:(CGRect)pageRect
{
    for (Annotation *textAnnot in textAnnotations) {
        CGFloat x = textAnnot.xPosValue * pageRect.size.width;
        CGFloat y = textAnnot.yPosValue * pageRect.size.height;
        CGFloat width = textAnnot.widthValue * pageRect.size.width;
        CGFloat height = textAnnot.heightValue * pageRect.size.height;
        CGRect annotRect = CGRectMake(x + 4.0, y + 5.0, width, height);
        
        UIColor *textColour = [UIColor colorWithHexString:textAnnot.colour];
        UIFont *font = [UIFont systemFontOfSize:8.0];
        
        CGContextSetFillColorWithColor(pdfContext, textColour.CGColor);
        //[textAnnot.title drawInRect:annotRect withFont:font];
        NSDictionary *dict = @{ NSFontAttributeName: font, NSForegroundColorAttributeName: textColour};
        [textAnnot.title drawInRect:annotRect withAttributes:dict];
    }
}


@end
