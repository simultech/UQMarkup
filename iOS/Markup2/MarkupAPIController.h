//
//  MarkupAPIController.h
//  Markup
//

#import <Foundation/Foundation.h>

#define kApiBase @"https://markup.sbms.uq.edu.au/"


@interface MarkupAPIController : NSObject

+ (MarkupAPIController *)sharedApi;

// Auth

+ (void)setBaseURL:(NSString *)base;

- (void)getMarkupLocationsWithSuccess:(void(^)(NSArray *locations))success
                           andFailure:(void(^)(NSError *error))failure;

- (void)loginWithUsername:(NSString *)username
              andPassword:(NSString *)password
               withSucess:(void(^)())success
               andFailure:(void(^)(NSError *error))failure;

- (void)logoutWithSucess:(void(^)())success
               andFailure:(void(^)(NSError *error))failure;

- (void)getUserDetailsWithSuccess:(void(^)())success
                       andFailure:(void(^)(NSError *error))failure;


// Downloading data - Projects and submission files
- (void)getMyProjectListWithSuccess:(void(^)(NSArray *projects))success
                          orFailure:(void(^)(NSError *error))failure;


- (void)downloadSubmissionFileWithId:(NSInteger)submissionId
                         withSuccess:(void (^)(NSString *tempFilePath))success
                             failure:(void (^)(NSError *error))failure
                         andProgress:(void (^)(float percentComplete))progress;

// Publishing - uploading submissions with marks and annotations
- (void)publishSubmissionWithId:(NSInteger)submissionId
                  andBundlePath:(NSString *)zipFilePath
                    withSuccess:(void (^)())success
                        failure:(void (^)(NSError *error))failure
                       progress:(void (^)(int64_t bytesWritten, int64_t totalBytesWritten, int64_t bytesExpectedToWrite))progress;

//TOS
- (void)agreeToTermsWithSucess:(void(^)())success
                    andFailure:(void(^)(NSError *error))failure;

- (void)checkHasAgreedToTermsWithSucess:(void(^)(NSString *hasAgreed))success
                             andFailure:(void(^)(NSError *error))fail;

- (void)isLatestVersionWithSuccess:(void(^)(NSDictionary *latestInfo))success
                             andFailure:(void(^)(NSError *error))failure;

@end
