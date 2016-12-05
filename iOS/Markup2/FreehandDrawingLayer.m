//
//  FreehandDrawingLayer.m
//  DrawLayerTest
//

#import "FreehandDrawingLayer.h"
#import "AnnotationSettingsManager.h"

#define kFreehandLayerMinDistanceSquared 16.0
#define kFreehandLayerMaxPoints 400.0

@interface FreehandDrawingLayer ()
@property (nonatomic, strong) NSMutableArray *pathsInCurrentAnnotation;
@property (nonatomic, strong) NSMutableArray *pathPointCounts;
@property (nonatomic, strong) NSMutableArray *coloursInCurrentAnnotation;
@property (nonatomic, strong) NSMutableArray *swInCurrentAnnotation;
@end

static UIColor *sc;
static CGFloat lw;

@implementation FreehandDrawingLayer {
    CGMutablePathRef _path; // Holds the current path for drawing
    CGMutablePathRef _wholePath; // Holds the current complete path under the pen (for persistence)
    int _numPoints;
    int _numPointsInWholePath;
    
    CGImageRef _bitmapImage;
    CGPoint _currentPoint;
    CGPoint _previousPoint;
    
    BOOL _hasStartedPath;
}

- (id)init
{
    self = [super init];
    if (self) {
        self.contentsScale = [UIScreen mainScreen].scale;
        self.shouldRasterize = YES;
        self.rasterizationScale = self.contentsScale;
        self.drawsAsynchronously = YES;
        self.needsDisplayOnBoundsChange = YES;
        
        self.pathsInCurrentAnnotation = [[NSMutableArray alloc] init];
        self.coloursInCurrentAnnotation = [[NSMutableArray alloc] init];
        self.swInCurrentAnnotation = [[NSMutableArray alloc] init];
        self.pathPointCounts = [[NSMutableArray alloc] init];
        _numPoints = 0;
        _numPointsInWholePath = 0;
    }
    
    return self;
}

- (id<CAAction>)actionForKey:(NSString *)event
{
    /* disable default animations for this layer's contents */
    if ([event isEqualToString:@"contents"]) {
        return nil;
    }
    
    return [super actionForKey:event];
}

- (void)drawInContext:(CGContextRef)ctx
{
    CGContextSaveGState(ctx);
    
    /* If we have a bitmap, draw it */
    if (_bitmapImage) {
        CGContextDrawImage(ctx, self.bounds, _bitmapImage);
    }
    
    /* If we have a path, stroke it */
    if (_path) {
        CGContextAddPath(ctx, _path);
        CGContextSetLineWidth(ctx, lw);
        CGContextSetStrokeColorWithColor(ctx, sc.CGColor);
        CGContextSetLineJoin(ctx, kCGLineJoinRound);
        CGContextSetLineCap(ctx, kCGLineCapRound);
        CGContextStrokePath(ctx);
    }

    /* If we have a single point, draw a circle at that point */
    if (_numPoints == 1) {
        CGContextSetFillColorWithColor(ctx, sc.CGColor);
        CGContextFillEllipseInRect(ctx, CGRectMake(_currentPoint.x - lw * 0.5, _currentPoint.y - lw * 0.5, lw, lw));
    }

    CGContextRestoreGState(ctx);
}

- (NSString *)name
{
    return @"FreehandDrawingLayer";
}

# pragma mark -
# pragma mark Drawing handlers
- (void)beginPathAtPoint:(CGPoint)point
{
    NSLog(@"BEGINNING PATH");
    _hasStartedPath = YES;
    _path = CGPathCreateMutable();
    _wholePath = CGPathCreateMutable();
    
    _currentPoint = point;
    CGPathMoveToPoint(_path, NULL, point.x, point.y);
    CGPathMoveToPoint(_wholePath, NULL, point.x, point.y);
    _numPoints++;
    _numPointsInWholePath++;
    
    //[self setNeedsDisplay];
}

- (void)addNextPoint:(CGPoint)point
{
    assert(_hasStartedPath);
    
    /* Check if the point is further than the min dist from the previous */
    CGFloat dx = point.x - _currentPoint.x;
    CGFloat dy = point.y - _currentPoint.y;
    
    if ((dx * dx + dy * dy) < kFreehandLayerMinDistanceSquared) {
        return;
    }
    
    /* Update current and previous points */
    _previousPoint = _currentPoint;
    _currentPoint = point;
    
    CGPathAddLineToPoint(_path, NULL, point.x, point.y);
    CGPathAddLineToPoint(_wholePath, NULL, point.x, point.y);
    _numPoints++;
    _numPointsInWholePath++;
    
    /* Flatten the path if it's too long */
    if (_numPoints > kFreehandLayerMaxPoints) {
        [self flattenPath];
    }
    
    /* Calculate the dirty area as rects for updating */
    CGFloat minX = fmin(_previousPoint.x, _currentPoint.x) - lw * 0.5;
    CGFloat minY = fmin(_previousPoint.y, _currentPoint.y) - lw * 0.5;
    CGFloat maxX = fmax(_previousPoint.x, _currentPoint.x) + lw * 0.5;
    CGFloat maxY = fmax(_previousPoint.y, _currentPoint.y) + lw * 0.5;
    CGRect dirtyRect = CGRectMake(minX, minY, (maxX - minX), (maxY - minY));
    
    [self setNeedsDisplayInRect:dirtyRect];
}

- (void)endPath
{
    //[self flattenPath];
    [self.pathsInCurrentAnnotation addObject:[UIBezierPath bezierPathWithCGPath:_wholePath]];
    [self.pathPointCounts addObject:@(_numPointsInWholePath)];
    _numPointsInWholePath = 0;
    [self.coloursInCurrentAnnotation addObject:[sc copy]];
    [self.swInCurrentAnnotation addObject:@(lw)];
    CGPathRelease(_path);
    CGPathRelease(_wholePath);
    _wholePath = nil;
    _path = nil;
    _hasStartedPath = NO;
    _numPoints = 0;
    //[self setNeedsDisplay];
}

- (void)flattenPath
{
    UIGraphicsBeginImageContextWithOptions(self.bounds.size, NO, self.contentsScale);
    CGContextRef ctx = UIGraphicsGetCurrentContext();
    CGContextSaveGState(ctx);
    CGContextTranslateCTM(ctx, 0.0, self.bounds.size.height);
    CGContextScaleCTM(ctx, 1.0, -1.0);
    [self drawInContext:ctx];
    UIImage *bgImage = UIGraphicsGetImageFromCurrentImageContext();
    CGImageRelease(_bitmapImage);
    _bitmapImage = CGImageRetain([bgImage CGImage]);
    
    CGContextRestoreGState(ctx);
    UIGraphicsEndImageContext();
    
    _numPoints = 0;
    CGPathRelease(_path);
    _path = CGPathCreateMutable();
    CGPathMoveToPoint(_path, NULL, _currentPoint.x, _currentPoint.y);
    [self setNeedsDisplay];
}

+ (void)setStrokeColour:(UIColor *)strokeColour
{
    DLog(@"%@", strokeColour);
    sc = strokeColour;
}

+ (void)setLineWidth:(CGFloat)lineWidth
{
    lw = lineWidth;
}

# pragma mark Baking methods
- (UIImage *)bakeSavedAnnotationWithXPerc:(CGFloat *)outXPerc
                                    yPerc:(CGFloat *)outYPerc
                                widthPerc:(CGFloat *)outWidthPerc
                                heightPerc:(CGFloat *)outHeightPerc
{
    if (!self.pathsInCurrentAnnotation || self.pathsInCurrentAnnotation.count == 0) {
        return nil;
    }
    
    CGRect renderRect = CGRectZero;
    for (UIBezierPath *bezier in self.pathsInCurrentAnnotation) {
        CGPathRef path = [bezier CGPath];
        if (CGRectIsEmpty(renderRect)) {
            renderRect = CGPathGetPathBoundingBox(path);
            if (CGRectIsEmpty(renderRect)) {
                CGPoint origin = CGPathGetCurrentPoint(path);
                renderRect.origin = origin;
                renderRect.size = CGSizeMake(1.0, 1.0);
            }
        } else {
            CGRect pathRect = CGPathGetBoundingBox(path);
            if (CGRectIsEmpty(pathRect)) {
                CGPoint pathOrigin = CGPathGetCurrentPoint(path);
                pathRect.origin = pathOrigin;
                pathRect.size = CGSizeMake(1.0, 1.0);
            }
            renderRect = CGRectUnion(renderRect, pathRect);
        }
    }
    
    CGPoint originalOrigin = renderRect.origin;
    renderRect.origin = CGPointZero;
    CGRect imageRect = CGRectInset(renderRect, -lw, -lw);
    
    UIGraphicsBeginImageContextWithOptions(imageRect.size, NO, self.contentsScale);
    CGContextRef ctx = UIGraphicsGetCurrentContext();
    CGContextTranslateCTM(ctx, -originalOrigin.x + lw, -originalOrigin.y + lw);
    CGContextSetLineWidth(ctx, lw);
    CGContextSetStrokeColorWithColor(ctx, sc.CGColor);
    CGContextSetLineJoin(ctx, kCGLineJoinRound);
    CGContextSetLineCap(ctx, kCGLineCapRound);
    
    int i = 0;
    for (UIBezierPath *bezier in self.pathsInCurrentAnnotation) {
        UIColor *col = [self.coloursInCurrentAnnotation objectAtIndex:i];
        CGFloat w = [[self.swInCurrentAnnotation objectAtIndex:i] floatValue];
        int np = [[self.pathPointCounts objectAtIndex:i] intValue];
        i++;
        
        CGPathRef path = [bezier CGPath];
        CGContextAddPath(ctx, path);
        CGContextSetStrokeColorWithColor(ctx, col.CGColor);
        CGContextSetLineWidth(ctx, w);
        CGContextStrokePath(ctx);
        
        if (np == 1) {
            CGPoint point = CGPathGetCurrentPoint(path);
            CGContextSetFillColorWithColor(ctx, col.CGColor);
            CGContextFillEllipseInRect(ctx, CGRectMake(point.x - w * 0.5, point.y - w * 0.5, w, w));
        }
        
        
    }
    
    UIImage *baked = UIGraphicsGetImageFromCurrentImageContext();
    
    UIGraphicsEndImageContext();
    
    if (outXPerc) {
        *outXPerc = (originalOrigin.x - lw) / self.bounds.size.width;
        *outYPerc = (originalOrigin.y - lw) / self.bounds.size.height;
        *outWidthPerc = imageRect.size.width / self.bounds.size.width;
        *outHeightPerc = imageRect.size.height / self.bounds.size.height;
    }
    
    [self.pathsInCurrentAnnotation removeAllObjects];
    [self.swInCurrentAnnotation removeAllObjects];
    [self.coloursInCurrentAnnotation removeAllObjects];
    [self.pathPointCounts removeAllObjects];
    
    return baked;
}

- (void)clearCanvas
{
    CGImageRelease(_bitmapImage);
    _bitmapImage = nil;
    [self setNeedsDisplay];
}

@end
