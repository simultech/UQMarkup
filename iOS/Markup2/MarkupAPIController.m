//
//  MarkupAPIController.m
//  Markup
//

#import "MarkupAPIController.h"
#import "AFNetworking/AFHTTPClient.h"
#import "AFNetworking/AFJSONRequestOperation.h"
#import <Security/Security.h>
#import "Project.h"
#import "Course.h"
#import "SubmissionDownload.h"
#import "Rubric.h"
#import "PublishController.h"
#import "NSData+MD5.h"

@interface MarkupAPIController ()

@property (nonatomic, strong) AFHTTPClient *client;
@property (nonatomic, strong) NSString *username;
@property (nonatomic, strong) NSString *password;

@property (nonatomic, strong) NSOperationQueue *uploadQueue;
@end

@implementation MarkupAPIController
@synthesize client;

static NSString *baseURL;
static BOOL loaded = NO;
static NSString *kApiSharedSecret = @"";

static MarkupAPIController *instance;

+ (MarkupAPIController *)sharedApi
{
    if (!loaded) {
        NSUserDefaults *defaults = [NSUserDefaults standardUserDefaults];
        if([defaults objectForKey:@"basepath"]) {
            baseURL = [defaults objectForKey:@"basepath"];
        } else {
            baseURL = kApiBase;
        }
        loaded = YES;
    }
    if (!instance) {
        instance = [[MarkupAPIController alloc] init];
        instance.client = [[AFHTTPClient alloc] initWithBaseURL:[NSURL URLWithString:baseURL]];
        [instance.client registerHTTPOperationClass:[AFJSONRequestOperation class]];
        
        NSUserDefaults *defaults = [NSUserDefaults standardUserDefaults];
        instance.username = [defaults objectForKey:@"username"];
        instance.password = [defaults objectForKey:@"password"];
        instance.uploadQueue = [[NSOperationQueue alloc] init];
        [instance.uploadQueue setMaxConcurrentOperationCount:1];
    }


    //get secret key from plist
    NSString *secretKeyPath = [[NSBundle mainBundle] pathForResource:@"SecretKey" ofType:@"plist"];
    NSDictionary *dict = [NSDictionary dictionaryWithContentsOfFile: secretKeyPath];
    NSString *secretKey = [dict objectForKey: @"SecretKey"];
    kApiSharedSecret = secretKey;

    return instance;
}

+ (void)setBaseURL:(NSString *)base {
    NSUserDefaults *defaults = [NSUserDefaults standardUserDefaults];
    [defaults setValue:base forKey:@"basepath"];
    baseURL = base;
    instance = nil;
    [MarkupAPIController sharedApi];
}

- (void)getMarkupLocationsWithSuccess:(void(^)(NSArray *locations))success
               andFailure:(void(^)(NSError *error))failure
{
    
    NSURLRequest *request = [[NSURLRequest alloc] initWithURL:[NSURL URLWithString:@"http://uqmarkup.ceit.uq.edu.au/locations.json"]];
    
    AFJSONRequestOperation *jsonOp = [[AFJSONRequestOperation alloc] initWithRequest:request];
    
    [jsonOp setCompletionBlockWithSuccess:^(AFHTTPRequestOperation *operation, id responseObject) {
        success([responseObject objectForKey:@"locations"]);
    } failure:^(AFHTTPRequestOperation *operation, NSError *error) {
        
        failure(error);
    }];
    
    [jsonOp start];
    
}

- (void)loginWithUsername:(NSString *)username
              andPassword:(NSString *)password
               withSucess:(void(^)())success
               andFailure:(void(^)(NSError *error))failure
{
    NSDictionary *params = [self addAuthToParams:@{ @"username": username, @"password": password }];
    
    NSURLRequest *request = [self.client requestWithMethod:@"POST" path:@"api/login" parameters:params];
    AFJSONRequestOperation *jsonOp = [[AFJSONRequestOperation alloc] initWithRequest:request];
    [jsonOp setCompletionBlockWithSuccess:^(AFHTTPRequestOperation *operation, id responseObject) {
        NSUserDefaults *defaults = [NSUserDefaults standardUserDefaults];
        [defaults setObject:username forKey:@"username"];
        [defaults setObject:password forKey:@"password"];
        [defaults synchronize];
        
        success();
    } failure:^(AFHTTPRequestOperation *operation, NSError *error) {
        
        failure(error);
    }];
    
    [jsonOp start];
}

- (void)agreeToTermsWithSucess:(void(^)())success
               andFailure:(void(^)(NSError *error))failure
{
    NSDictionary *params = [self addAuthToParams:[NSDictionary dictionary]];
    NSURLRequest *request = [self.client requestWithMethod:@"POST" path:@"api/setAgreedToTOS" parameters:params];
    AFJSONRequestOperation *jsonOp = [[AFJSONRequestOperation alloc] initWithRequest:request];
    [jsonOp setCompletionBlockWithSuccess:^(AFHTTPRequestOperation *operation, id responseObject) {
        success();
    } failure:^(AFHTTPRequestOperation *operation, NSError *error) {
        failure(error);
    }];
    [jsonOp start];
}

- (void)isLatestVersionWithSuccess:(void(^)(NSDictionary *latestInfo))success
                        andFailure:(void(^)(NSError *error))failure {
    NSBundle *bundle = [NSBundle mainBundle];
    NSString *appVersion = [bundle objectForInfoDictionaryKey:(NSString *)@"CFBundleShortVersionString"];
    NSString *appBuildNumber = [bundle objectForInfoDictionaryKey:(NSString *)kCFBundleVersionKey];
    NSDictionary *params = [self addAuthToParams:@{ @"version": appVersion, @"build": appBuildNumber }];
    NSURLRequest *request = [self.client requestWithMethod:@"POST" path:@"api/isLatestVersion" parameters:params];
    AFJSONRequestOperation *jsonOp = [[AFJSONRequestOperation alloc] initWithRequest:request];
    [jsonOp setCompletionBlockWithSuccess:^(AFHTTPRequestOperation *operation, id responseObject) {
        success([responseObject objectForKey:@"response"]);
    } failure:^(AFHTTPRequestOperation *operation, NSError *error) {
        failure(error);
    }];
    [jsonOp start];
}

- (void)checkHasAgreedToTermsWithSucess:(void(^)(NSString *hasAgreed))success
                    andFailure:(void(^)(NSError *error))failure
{
    NSDictionary *params = [self addAuthToParams:[NSDictionary dictionary]];
    NSURLRequest *request = [self.client requestWithMethod:@"POST" path:@"api/hasAgreedToTOS" parameters:params];
    AFJSONRequestOperation *jsonOp = [[AFJSONRequestOperation alloc] initWithRequest:request];
    [jsonOp setCompletionBlockWithSuccess:^(AFHTTPRequestOperation *operation, id responseObject) {
        success([responseObject objectForKey:@"response"]);
    } failure:^(AFHTTPRequestOperation *operation, NSError *error) {
        failure(error);
    }];
    [jsonOp start];
}

- (void)logoutWithSucess:(void(^)())success
              andFailure:(void(^)(NSError *error))failure
{
    NSDictionary *params = [self addAuthToParams:[NSDictionary dictionary]];
    NSURLRequest *request = [self.client requestWithMethod:@"GET" path:@"api/logout" parameters:params];
    
    AFJSONRequestOperation *jsonOp = [[AFJSONRequestOperation alloc] initWithRequest:request];
    [jsonOp setCompletionBlockWithSuccess:^(AFHTTPRequestOperation *operation, id responseObject) {
        NSUserDefaults *defaults = [NSUserDefaults standardUserDefaults];
        [defaults removeObjectForKey:@"username"];
        [defaults removeObjectForKey:@"password"];
        success();
    } failure:^(AFHTTPRequestOperation *operation, NSError *error) {
        NSUserDefaults *defaults = [NSUserDefaults standardUserDefaults];
        [defaults removeObjectForKey:@"username"];
        [defaults removeObjectForKey:@"password"];
        DLog(@"Failed due to: %d: %@", operation.response.statusCode, operation.responseString);
        failure(error);
    }];
    
    [jsonOp start];
}

- (void)getUserDetailsWithSuccess:(void(^)())success
                       andFailure:(void(^)(NSError *error))failure
{
    NSDictionary *params = [self addAuthToParams:[NSDictionary dictionary]];
    NSURLRequest *request = [self.client requestWithMethod:@"GET" path:@"api/userdetails" parameters:params];
    AFJSONRequestOperation *jsonOp = [[AFJSONRequestOperation alloc] initWithRequest:request];
    [jsonOp setCompletionBlockWithSuccess:^(AFHTTPRequestOperation *operation, id responseObject) {
        
    } failure:^(AFHTTPRequestOperation *operation, NSError *error) {
        
    }];
    
    [jsonOp start];
}

- (void)getMyProjectListWithSuccess:(void (^)(NSArray *))success
                          orFailure:(void (^)(NSError *))failure
{
    NSDictionary *params = [self addAuthToParams:[NSDictionary dictionary]];
    
    NSURLRequest *request = [self.client requestWithMethod:@"GET" path:@"api/projectlist" parameters:params];
    AFJSONRequestOperation *jsonOp = [[AFJSONRequestOperation alloc] initWithRequest:request];
    [jsonOp setCompletionBlockWithSuccess:^(AFHTTPRequestOperation *operation, id responseObject) {
        NSMutableArray *projects = [[NSMutableArray alloc] init];
        for (NSDictionary *projDict in [responseObject objectForKey:@"response"]) {
            Project *project = [MarkupAPIController _configureProjectFromDict:projDict];
            [projects addObject:project];
        }
    
        success([projects copy]);
    } failure:^(AFHTTPRequestOperation *operation, NSError *error) {
        if (operation.response.statusCode == 403) {
            failure([[NSError alloc] initWithDomain:@"MarkupAPIDomain" code:403 userInfo:nil]);
        }
        DLog(@"Failed due to: %d: %@", operation.response.statusCode, operation.responseString);
    }];
    
    [jsonOp start];
    
}

- (void)downloadSubmissionFileWithId:(NSInteger)submissionId
                         withSuccess:(void (^)(NSString *tempFilePath))success
                             failure:(void (^)(NSError *error))failure
                         andProgress:(void (^)(float percentComplete))progress
{
    NSDictionary *params = [self addAuthToParams:[NSDictionary dictionary]];
    NSString *requestPath = [NSString stringWithFormat:@"api/submissionFile/%d", submissionId];
    NSURLRequest *request = [self.client requestWithMethod:@"GET" path:requestPath parameters:params];
    AFHTTPRequestOperation *requestOp = [[AFHTTPRequestOperation alloc] initWithRequest:request];
    
    NSString *tempDirectory = NSTemporaryDirectory();
    NSString *tempFilePath = [tempDirectory stringByAppendingPathComponent:[NSString stringWithFormat:@"submission_%d.pdf",submissionId]];
    NSOutputStream *outputStream = [NSOutputStream outputStreamToFileAtPath:tempFilePath append:NO];
    [outputStream open];
    
    [requestOp setCompletionBlockWithSuccess:^(AFHTTPRequestOperation *operation, id responseObject) {
        [outputStream close];
        BOOL fail = YES;
        NSString *md5 = @"";
        NSData *nsData = [NSData dataWithContentsOfFile:tempFilePath];
        if (nsData) {
            md5 = [nsData MD5];
        }
        NSLog(@"MD5 %@",[[[operation response] allHeaderFields] objectForKey:@"Content-MD5"]);
        if([[[[operation response] allHeaderFields] objectForKey:@"Content-MD5"] isEqualToString:md5]) {
            fail = NO;
        }
        if(fail) {
            failure(nil);
        } else {
            success(tempFilePath);   
        }
    } failure:^(AFHTTPRequestOperation *operation, NSError *error) {
        [outputStream close];
        DLog(@"%@\n%@", error, operation.responseString);
        failure(error);
    }];
    
    [requestOp setDownloadProgressBlock:^(NSInteger bytesRead, long long totalBytesRead, long long totalBytesExpectedToRead) {
        float percentComplete = totalBytesRead / (float)totalBytesExpectedToRead;
        progress(percentComplete);
    }];
    
    [requestOp setOutputStream:outputStream];
    
    [requestOp start];
}

- (void)publishSubmissionWithId:(NSInteger)submissionId
                  andBundlePath:(NSString *)zipFilePath
                    withSuccess:(void (^)())success
                        failure:(void (^)(NSError *error))failure
                       progress:(void (^)(int64_t bytesWritten, int64_t totalBytesWritten, int64_t bytesExpectedToWrite))progress
{
    NSDictionary *params = [self addAuthToParams:@{}];
    NSString *requestPath = [NSString stringWithFormat:@"api/uploadSubmission/%d/", submissionId];
    NSData *zipFileData = [NSData dataWithContentsOfFile:zipFilePath];
    NSURLRequest *request = [self.client multipartFormRequestWithMethod:@"POST" path:requestPath parameters:params constructingBodyWithBlock:^(id<AFMultipartFormData> formData) {
        
        [formData appendPartWithFileData:zipFileData name:@"submissiondata" fileName:[zipFilePath lastPathComponent] mimeType:@"application/zip"];
    }];
    
    AFHTTPRequestOperation *requestOp = [[AFHTTPRequestOperation alloc] initWithRequest:request];
    [requestOp setCompletionBlockWithSuccess:^(AFHTTPRequestOperation *operation, id responseObject) {
        
        success();
    } failure:^(AFHTTPRequestOperation *operation, NSError *error) {
        DLog(@"%@, %@", error, operation.responseString);
        failure(error);
    }];
    
    [requestOp setUploadProgressBlock:^(NSInteger bytesWritten, long long totalBytesWritten, long long totalBytesExpectedToWrite) {
        
        progress(bytesWritten, totalBytesWritten, totalBytesExpectedToWrite);
    }];
    
    [self.uploadQueue addOperation:requestOp];
}

#pragma mark -
#pragma mark Private utility methods

- (NSDictionary *)addAuthToParams:(NSDictionary *)paramsDict
{
    NSMutableDictionary *paramsMut = [[NSMutableDictionary alloc] initWithDictionary:paramsDict copyItems:YES];
    if ([kApiSharedSecret isEqualToString:@""]) {
        [NSException raise:@"PRIVATE KEY NOT SET" format:@"value of kApiSharedSecret (%@) is invalid", kApiSharedSecret];
    }
    [paramsMut setObject:kApiSharedSecret forKey:@"secret"];
    return [paramsMut copy];
}
        
+ (NSDateFormatter *)shortDateFormatter
{
    static NSDateFormatter *formattedDate;
    if (!formattedDate) {
        formattedDate = [[NSDateFormatter alloc] init];
        [formattedDate setDateFormat:@"yyyy-MM-dd"];
    }
    return formattedDate;
}

+ (Project *)_configureProjectFromDict:(NSDictionary *)projDict
{
    Project *project = [[Project alloc] init];
    project.projectId = [projDict valueForKeyPath:@"Project.id"];
    project.projectName = [projDict valueForKeyPath:@"Project.name"];
    project.projectDescription = [projDict valueForKeyPath:@"Project.description"];
    project.startDate = [[MarkupAPIController shortDateFormatter] dateFromString:[projDict valueForKeyPath:@"Project.start_date"]];
    project.endDate = [[MarkupAPIController shortDateFormatter] dateFromString:[projDict valueForKeyPath:@"Project.end_date"]];
    project.submissionDate = [[MarkupAPIController shortDateFormatter] dateFromString:[projDict valueForKeyPath:@"Project.submission_date"]];
    
    // Set up the associated course
    Course *course = [[Course alloc] init];
    course.courseCode = [projDict valueForKeyPath:@"Course.coursecode"];
    course.shadowCode = [projDict valueForKeyPath:@"Course.shadowcode"];
    course.courseId = [projDict valueForKeyPath:@"Course.uid"];
    course.courseName = [projDict valueForKeyPath:@"Course.name"];
    course.year = [projDict valueForKeyPath:@"Course.year"];
    course.semester = [projDict valueForKeyPath:@"Course.semester"];
    project.course = course;
    
    // Set up rubrics
    NSArray *rubricsArray = [projDict valueForKeyPath:@"Rubric"];
    NSMutableArray *rubrics = [[NSMutableArray alloc] init];
    for (NSDictionary *rubricDict in rubricsArray) {
        Rubric *rubric = [[Rubric alloc] init];
        rubric.rubricId = [[rubricDict objectForKey:@"id"] integerValue];
        rubric.rubricMeta = [rubricDict objectForKey:@"meta"];
        rubric.rubricName = [rubricDict objectForKey:@"name"];
        rubric.projectId = [[rubricDict objectForKey:@"project_id"] integerValue];
        rubric.rubricSection = [NSString stringWithFormat:@"%@",[rubricDict objectForKey:@"section"]];
        if([[rubricDict objectForKey:@"type"] isEqualToString:@"table"]) {
            rubric.rubricType = RubricTypeTable;
        }
        else if([[rubricDict objectForKey:@"type"] isEqualToString:@"number"]) {
            rubric.rubricType = RubricTypeNumber;
        }
        else if([[rubricDict objectForKey:@"type"] isEqualToString:@"boolean"]) {
            rubric.rubricType = RubricTypeBoolean;
        }
        else if([[rubricDict objectForKey:@"type"] isEqualToString:@"text"]) {
            rubric.rubricType = RubricTypeText;
        }
        if([rubricDict objectForKey:@"value"] != nil) {
            rubric.rubricValue = [rubricDict objectForKey:@"value"];
        }
        [rubrics addObject:rubric];
    }
    project.rubrics = [rubrics copy];
    
    // Add submissions
    NSArray *submissionsArray = [projDict valueForKeyPath:@"Submission"];
    NSMutableArray *submissions = [[NSMutableArray alloc] init];
    NSLog(@"%@",submissionsArray);
    for (NSDictionary *submissionDict in submissionsArray) {
        SubmissionDownload *sub = [[SubmissionDownload alloc] init];
        NSLog(@"%@",[submissionDict valueForKeyPath:@"title"]);
        sub.submissionId = [[submissionDict valueForKeyPath:@"id"] intValue];
        sub.title = [submissionDict valueForKeyPath:@"title"];
        sub.uqId = [submissionDict valueForKeyPath:@"uqid"];
        sub.project = project;
        [submissions addObject:sub];
        
        sub.submission = [Submission findFirstByAttribute:@"submissionId" withValue:@(sub.submissionId)];
        
    }
    project.submissions = [submissions copy];
    
    return project;
}

@end
