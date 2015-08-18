//
//  AMSlideMenuMainViewController.m
//  AMSlideMenu
//
// The MIT License (MIT)
//
// Created by : arturdev
// Copyright (c) 2014 SocialObjects Software. All rights reserved.
//
// Permission is hereby granted, free of charge, to any person obtaining a copy of
// this software and associated documentation files (the "Software"), to deal in
// the Software without restriction, including without limitation the rights to
// use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
// the Software, and to permit persons to whom the Software is furnished to do so,
// subject to the following conditions:
//
// The above copyright notice and this permission notice shall be included in all
// copies or substantial portions of the Software.
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
// FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
// COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
// IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
// CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE

#import "AMSlideMenuMainViewController.h"

#import "AMSlideMenuContentSegue.h"
#import "AMSlideMenuLeftMenuSegue.h"
#import "AMSlideMenuRightMenuSegue.h"

#define kPanMinTranslationX 15.0f

#define kMenuTransformScale CATransform3DMakeScale(0.9, 0.9, 0.9)
#define kMenuLayerInitialOpacity 0.4f

#define kAutoresizingMaskAll UIViewAutoresizingFlexibleWidth|UIViewAutoresizingFlexibleHeight|UIViewAutoresizingFlexibleLeftMargin|UIViewAutoresizingFlexibleRightMargin|UIViewAutoresizingFlexibleTopMargin|UIViewAutoresizingFlexibleBottomMargin

typedef enum {
  AMSlidePanningStateStopped,
  AMSlidePanningStateLeft,
  AMSlidePanningStateRight
} AMSlidePanningState;

@interface AMSlideMenuMainViewController ()<UIGestureRecognizerDelegate>
{
    AMSlidePanningState panningState;
    CGFloat panningPreviousPosition;
    NSDate* panningPreviousEventDate;
    CGFloat panningXSpeed;  // panning speed expressed in px/ms
    bool panStarted;
    UIInterfaceOrientation initialOrientation;
}
@property (strong, nonatomic) AMSlideMenuLeftMenuSegue *leftSegue;
@property (strong, nonatomic) AMSlideMenuRightMenuSegue *rightSegue;

// Add transparent overlay view to currentActiveNVC's  view to disable all touches, when menu is opened
@property (strong, nonatomic) UIView *overlayView;

@property (strong, nonatomic) UIView *darknessView;

@property (strong, nonatomic) UIView *statusBarView;

@property (strong, nonatomic) UINavigationController *initialViewController;

@end

static NSMutableArray *allInstances;

@implementation AMSlideMenuMainViewController

/*----------------------------------------------------*/
#pragma mark - Lifecycle -
/*----------------------------------------------------*/

- (void)viewDidLoad
{
    [super viewDidLoad];
    if (!allInstances)
    {
        allInstances = [NSMutableArray array];
    }
    
    NSValue *value = [NSValue valueWithNonretainedObject:self];
    [allInstances addObject:value];
//    [allInstances addObject:self];
    
    [[NSNotificationCenter defaultCenter] addObserver:self selector:@selector(handleInterfaceOrientationChangedNotification:) name:UIDeviceOrientationDidChangeNotification object:nil];
    initialOrientation = [UIApplication sharedApplication].statusBarOrientation;
    [self setup];
}

- (void)handleInterfaceOrientationChangedNotification:(NSNotification *)not
{
    if ([self.currentActiveNVC shouldAutorotate])
    {
        CGRect bounds = self.view.bounds;
        self.rightMenu.view.frame = CGRectMake(bounds.size.width - [self rightMenuWidth],0,[self rightMenuWidth],bounds.size.height);
        self.leftMenu.view.frame = CGRectMake(0,0,bounds.size.width,bounds.size.height);
        if (self.overlayView && self.overlayView.superview)
        {
            self.overlayView.frame = CGRectMake(0, 0, self.currentActiveNVC.view.frame.size.width, self.currentActiveNVC.view.frame.size.height);
        }

        double delayInSeconds = 0.25f;
        dispatch_time_t popTime = dispatch_time(DISPATCH_TIME_NOW, (int64_t)(delayInSeconds * NSEC_PER_SEC));
        dispatch_after(popTime, dispatch_get_main_queue(), ^(void){
            [self configureSlideLayer:self.currentActiveNVC.view.layer];
        });
        
        
        //fix orientation for iPhone
        if (UI_USER_INTERFACE_IDIOM() != UIUserInterfaceIdiomPad)
        {
            UIInterfaceOrientation toInterfaceOrientation = [[UIApplication sharedApplication] statusBarOrientation];
            CGRect frame = self.currentActiveNVC.navigationBar.frame;
            if (toInterfaceOrientation == UIInterfaceOrientationPortrait || toInterfaceOrientation == UIInterfaceOrientationPortraitUpsideDown) {
                frame.size.height = 44;
            } else {
                frame.size.height = 32;
            }
            self.currentActiveNVC.navigationBar.frame = frame;
        }
    }
}

- (void)willRotateToInterfaceOrientation:(UIInterfaceOrientation)toInterfaceOrientation duration:(NSTimeInterval)duration
{
    if ([self.currentActiveNVC shouldAutorotate])
    {
        self.currentActiveNVC.view.layer.shadowOpacity = 0;
    }
}

- (void)dealloc
{
    NSMutableArray *arr = [allInstances mutableCopy];
    for (NSValue *value in arr)
    {
        AMSlideMenuMainViewController *mainVC = [value nonretainedObjectValue];
        if (mainVC == self) {
            [allInstances removeObject:value];
        }
    }
    
    [[NSNotificationCenter defaultCenter] removeObserver:self];
}

/*----------------------------------------------------*/
#pragma mark - Static methods -
/*----------------------------------------------------*/

+ (NSArray *)allInstances
{
    return allInstances;
}

+ (AMSlideMenuMainViewController *)getInstanceForVC:(UIViewController *)vc
{

    if (allInstances.count == 1)
        return [allInstances[0] nonretainedObjectValue];
    
    for (NSValue *value in allInstances)
    {
        AMSlideMenuMainViewController *mainVC = [value nonretainedObjectValue];
        if (mainVC.currentActiveNVC == vc.navigationController || mainVC.currentActiveNVC == vc)
        {
            return mainVC;
        }
    }
    return nil;
}

/*----------------------------------------------------*/
#pragma mark - Datasource -
/*----------------------------------------------------*/

- (CGFloat)leftMenuWidth
{
    return 250;
}

- (CGFloat)rightMenuWidth
{
    return 250;
}

- (CGFloat) openAnimationDuration
{
    return 0.35f;
}

- (CGFloat) closeAnimationDuration
{
    return 0.35f;
}

- (UIViewAnimationOptions) openAnimationCurve
{
    return UIViewAnimationOptionCurveLinear;
}

- (UIViewAnimationOptions) closeAnimationCurve
{
    return UIViewAnimationOptionCurveLinear;
}

- (void)configureLeftMenuButton:(UIButton *)button
{
    
}

- (void)configureRightMenuButton:(UIButton *)button
{
    
}

- (AMPrimaryMenu)primaryMenu
{
    return AMPrimaryMenuLeft;
}

- (NSIndexPath *)initialIndexPathForLeftMenu
{
    return [NSIndexPath indexPathForRow:0 inSection:0];
}

- (NSIndexPath *)initialIndexPathForRightMenu
{
    return [NSIndexPath indexPathForRow:0 inSection:0];    
}

- (NSString *)segueIdentifierForIndexPathInLeftMenu:(NSIndexPath *)indexPath
{
    return @"";
}

- (NSString *)segueIdentifierForIndexPathInRightMenu:(NSIndexPath *)indexPath
{
    return @"";
}

- (void) configureSlideLayer:(CALayer *)layer
{
    layer.shadowColor = [UIColor grayColor].CGColor;
    layer.shadowOpacity = 1;
    layer.shadowOffset = CGSizeMake(0, 0);
    layer.masksToBounds = NO;
    layer.shadowPath =[UIBezierPath bezierPathWithRect:layer.bounds].CGPath;
}

- (CGFloat)panGestureWarkingAreaPercent
{
    return 100.0f;
}

- (BOOL)deepnessForLeftMenu
{
    return NO;
}

- (BOOL)deepnessForRightMenu
{
    return NO;
}

- (CGFloat)maxDarknessWhileLeftMenu
{
    return 0;
}

- (CGFloat)maxDarknessWhileRightMenu
{
    return 0;
}

/*----------------------------------------------------*/
#pragma mark - Private methods -
/*----------------------------------------------------*/

- (void)setRightMenu:(AMSlideMenuRightTableViewController *)rightMenu
{
    _rightMenu = rightMenu;
    
    CGRect frame = _rightMenu.view.frame;
    frame.size.width = [self rightMenuWidth];
    _rightMenu.view.frame = frame;
}

- (void)configureDarknessView
{
    [self.darknessView removeFromSuperview];

    self.darknessView = [[UIView alloc] initWithFrame:self.currentActiveNVC.view.bounds];
    self.darknessView.backgroundColor = [UIColor blackColor];

    switch (self.menuState) {
        case AMSlideMenuClosed:
            self.darknessView.alpha = 0;
            break;
        case AMSlideMenuLeftOpened:
            self.darknessView.alpha = [self maxDarknessWhileLeftMenu];
            break;
        case AMSlideMenuRightOpened:
            self.darknessView.alpha = [self maxDarknessWhileRightMenu];
            break;
        default:
            self.darknessView.alpha = 0;
            break;
    }
    self.darknessView.layer.zPosition = 1;
    
    self.darknessView.autoresizingMask = kAutoresizingMaskAll;
    [self.currentActiveNVC.view addSubview:self.darknessView];
}

// calls when pan gesture starting and direction is left
- (void)rightMenuWillReveal
{
    [self configureDarknessView];
}

// calls when pan gesture starting and direction is right
- (void)leftMenuWillReveal
{
    [self configureDarknessView];
}

#pragma mark - Gesture recognizer delegates
- (BOOL)gestureRecognizer:(UIGestureRecognizer *)gestureRecognizer shouldReceiveTouch:(UITouch *)touch {
    if ([touch.view isKindOfClass:[UISlider class]]) {
        // prevent recognizing touches on the slider
        return NO;
    }
    return YES;
}

- (BOOL)gestureRecognizerShouldBegin:(UIGestureRecognizer *)gestureRecognizer
{
    CGPoint velocity = [self.panGesture velocityInView:self.panGesture.view];
    BOOL isHorizontalGesture = fabs(velocity.y) < fabs(velocity.x);
    
    return isHorizontalGesture;
}

- (BOOL)gestureRecognizer:(UIGestureRecognizer *)gestureRecognizer shouldRecognizeSimultaneouslyWithGestureRecognizer:(UIGestureRecognizer *)otherGestureRecognizer
{
    CGPoint velocity = [self.panGesture velocityInView:self.panGesture.view];
    BOOL isHorizontalGesture = fabs(velocity.y) < fabs(velocity.x);
    
    if ([otherGestureRecognizer.view isKindOfClass:[UITableView class]]) {
        if (isHorizontalGesture) {
            BOOL directionIsLeft = velocity.x < 0;
            if (directionIsLeft) {
                self.panGesture.enabled = NO;
                self.panGesture.enabled = YES;
                if (self.rightMenu) {
                    return NO;
                } else {
                    return YES;
                }
            } else {
                //if direction is to right
                UITableView *tableView = (UITableView *)otherGestureRecognizer.view;
                CGPoint point = [otherGestureRecognizer locationInView:tableView];
                NSIndexPath *indexPath = [tableView indexPathForRowAtPoint:point];
                UITableViewCell *cell = [tableView cellForRowAtIndexPath:indexPath];
                if (cell.isEditing) {
                    self.panGesture.enabled = NO;
                    self.panGesture.enabled = YES;
                    return YES;
                }
            }
        }
    } else if ([otherGestureRecognizer.view isKindOfClass:NSClassFromString(@"UITableViewCellScrollView")]) {
        return YES;
    }
    
    return NO;
}

#pragma mark -
- (void)setup
{
    self.view.backgroundColor = [UIColor blackColor];
    
    self.isInitialStart = YES;
    
    self.tapGesture = [[UITapGestureRecognizer alloc] init];
    self.panGesture = [[UIPanGestureRecognizer alloc] init];
    
    [self.tapGesture addTarget:self action:@selector(handleTapGesture:)];
    [self.panGesture addTarget:self action:@selector(handlePanGesture:)];
    
    self.tapGesture.cancelsTouchesInView = YES;
    self.panGesture.cancelsTouchesInView = YES;
    
    self.panGesture.delegate = self;
    
    /**********************************
     *  If using storyboard
     **********************************/
#ifndef AMSlideMenuWithoutStoryboards    
    if ([self primaryMenu] == AMPrimaryMenuLeft)
    {
        @try
        {
            [self performSegueWithIdentifier:@"leftMenu" sender:self];

            @try {
                [self performSegueWithIdentifier:@"rightMenu" sender:self];
            }
            @catch (NSException *exception) {
                
            }
        }
        @catch (NSException *exception)
        {
            [self performSegueWithIdentifier:@"rightMenu" sender:self];
            NSLog(@"WARNING: You setted primaryMenu to left , but you have no segue with identifier 'leftMenu'");
        }
    }
    else if ([self primaryMenu] == AMPrimaryMenuRight)
    {
        @try
        {
            [self performSegueWithIdentifier:@"rightMenu" sender:self];
            
            @try {
                [self performSegueWithIdentifier:@"leftMenu" sender:self];
            }
            @catch (NSException *exception) {
        
            }
        }
        @catch (NSException *exception)
        {
            [self performSegueWithIdentifier:@"leftMenu" sender:self];
            NSLog(@"WARNING: You setted primaryMenu to right , but you have no segue with identifier 'rightMenu'");
        }
    }
    /***********************************/
    
    /***********************************
     *    If not using storyboards
     ***********************************/
    
#else
    if ([self primaryMenu] == AMPrimaryMenuLeft)
    {
        if (self.leftMenu)
        {
            self.leftSegue = [[AMSlideMenuLeftMenuSegue alloc] initWithIdentifier:@"leftMenu" source:self destination:self.leftMenu];
            [self.leftSegue perform];

            // Fixing strange bug with iPad iOS6 when starting whit landscape orientation
            if (SYSTEM_VERSION_LESS_THAN(@"7.0") && UI_USER_INTERFACE_IDIOM() == UIUserInterfaceIdiomPad && UIInterfaceOrientationIsLandscape([UIApplication sharedApplication].statusBarOrientation)) {

                dispatch_after(dispatch_time(DISPATCH_TIME_NOW, (int64_t)(0 * NSEC_PER_SEC)), dispatch_get_main_queue(), ^{
                    [self.leftSegue perform];
                });
            }
        }
        if (self.rightMenu)
        {
            self.rightSegue = [[AMSlideMenuRightMenuSegue alloc] initWithIdentifier:@"rightSegue" source:self destination:self.rightMenu];
            [self.rightSegue perform];

            // Fixing strange bug with iPad iOS6 when starting whit landscape orientation
            if (SYSTEM_VERSION_LESS_THAN(@"7.0") && UI_USER_INTERFACE_IDIOM() == UIUserInterfaceIdiomPad && UIInterfaceOrientationIsLandscape([UIApplication sharedApplication].statusBarOrientation)) {

                dispatch_after(dispatch_time(DISPATCH_TIME_NOW, (int64_t)(0 * NSEC_PER_SEC)), dispatch_get_main_queue(), ^{
                    [self.rightSegue perform];
                });
            }
        }
    }
    else if ([self primaryMenu] == AMPrimaryMenuRight)
    {
        if (self.rightMenu)
        {
            self.rightSegue = [[AMSlideMenuRightMenuSegue alloc] initWithIdentifier:@"rightSegue" source:self destination:self.rightMenu];
            [self.rightSegue perform];
            
            // Fixing strange bug with iPad iOS6 when starting whit landscape orientation
            if (SYSTEM_VERSION_LESS_THAN(@"7.0") && UI_USER_INTERFACE_IDIOM() == UIUserInterfaceIdiomPad && UIInterfaceOrientationIsLandscape([UIApplication sharedApplication].statusBarOrientation)) {
                
                dispatch_after(dispatch_time(DISPATCH_TIME_NOW, (int64_t)(0 * NSEC_PER_SEC)), dispatch_get_main_queue(), ^{
                    [self.rightSegue perform];
                });
            }
        }
        if (self.leftMenu)
        {
            self.leftSegue = [[AMSlideMenuLeftMenuSegue alloc] initWithIdentifier:@"leftMenu" source:self destination:self.leftMenu];
            [self.leftSegue perform];
            
            // Fixing strange bug with iPad iOS6 when starting whit landscape orientation
            if (SYSTEM_VERSION_LESS_THAN(@"7.0") && UI_USER_INTERFACE_IDIOM() == UIUserInterfaceIdiomPad && UIInterfaceOrientationIsLandscape([UIApplication sharedApplication].statusBarOrientation)) {
                
                dispatch_after(dispatch_time(DISPATCH_TIME_NOW, (int64_t)(0 * NSEC_PER_SEC)), dispatch_get_main_queue(), ^{
                    [self.leftSegue perform];
                });
            }
        }
    }
#endif
    /*******************************************/
    
    
    [self.currentActiveNVC.view addGestureRecognizer:self.panGesture];
    
    self.isInitialStart = NO;
}

- (void)viewWillAppear:(BOOL)animated
{
    [super viewWillAppear:animated];
    
    if (self.leftMenu && [self deepnessForLeftMenu])
    {
        self.leftMenu.view.layer.transform = kMenuTransformScale;
        self.leftMenu.view.layer.opacity = kMenuLayerInitialOpacity;
        self.leftMenu.view.autoresizingMask = kAutoresizingMaskAll;
        self.leftMenu.view.hidden = YES;
    }
    if (self.rightMenu && [self deepnessForRightMenu])
    {
        self.rightMenu.view.layer.transform = kMenuTransformScale;
        self.rightMenu.view.layer.opacity = kMenuLayerInitialOpacity;
        self.rightMenu.view.autoresizingMask = kAutoresizingMaskAll;
        self.rightMenu.view.hidden = YES;
    }
    
    // Disabling scrollsToTop for menu's tableviews
    self.leftMenu.tableView.scrollsToTop = NO;
    self.rightMenu.tableView.scrollsToTop = NO;

}

/*----------------------------------------------------*/
#pragma mark - Public Actions -
/*----------------------------------------------------*/

- (void)openLeftMenu
{
    [self openLeftMenuAnimated:YES];
}

- (void)openLeftMenuAnimated:(BOOL)animated
{
    if (self.slideMenuDelegate && [self.slideMenuDelegate respondsToSelector:@selector(leftMenuWillOpen)])
        [self.slideMenuDelegate leftMenuWillOpen];
    
    if (!self.darknessView)
        [self configureDarknessView];
    
    self.rightMenu.view.hidden = YES;
    self.leftMenu.view.hidden = NO;
    
    CGRect frame = self.currentActiveNVC.view.frame;
    frame.origin.x = [self leftMenuWidth];
    
    [UIView animateWithDuration: animated ? self.openAnimationDuration : 0 delay:0.0 options:self.openAnimationCurve animations:^{
        self.currentActiveNVC.view.frame = frame;
        
        if ([self deepnessForLeftMenu])
        {
            self.leftMenu.view.layer.transform = CATransform3DMakeScale(1.0, 1.0, 1.0);
            self.leftMenu.view.layer.opacity = 1.0f;
        }
        
        if (self.statusBarView)
        {
            self.statusBarView.layer.opacity = 0;
        }
        
        self.darknessView.alpha = [self maxDarknessWhileLeftMenu];
    } completion:^(BOOL finished) {
        [self addGestures];
        [self enableGestures];
        self.menuState = AMSlideMenuLeftOpened;
        
        if (self.slideMenuDelegate && [self.slideMenuDelegate respondsToSelector:@selector(leftMenuDidOpen)])
            [self.slideMenuDelegate leftMenuDidOpen];
    }];
}

- (void)openRightMenu
{
    [self openRightMenuAnimated:YES];
}

- (void)openRightMenuAnimated:(BOOL)animated
{
    if (self.slideMenuDelegate && [self.slideMenuDelegate respondsToSelector:@selector(rightMenuWillOpen)])
        [self.slideMenuDelegate rightMenuWillOpen];
    if (!self.darknessView)
        [self configureDarknessView];
    
    self.rightMenu.view.hidden = NO;
    self.leftMenu.view.hidden = YES;
    
    CGRect frame = self.currentActiveNVC.view.frame;
    frame.origin.x = -1 *[self rightMenuWidth];
    
    [UIView animateWithDuration:animated ? self.openAnimationDuration : 0 delay:0.0 options:self.openAnimationCurve animations:^{
        self.currentActiveNVC.view.frame = frame;
        
        if ([self deepnessForRightMenu])
        {
            self.rightMenu.view.layer.transform = CATransform3DMakeScale(1.0, 1.0, 1.0);
            self.rightMenu.view.layer.opacity = 1.0f;
        }
        
        if (self.statusBarView)
        {
            self.statusBarView.layer.opacity = 0;
        }
        
        self.darknessView.alpha = [self maxDarknessWhileRightMenu];
    } completion:^(BOOL finished) {
        [self addGestures];
        [self enableGestures];
        self.menuState = AMSlideMenuRightOpened;
        
        if (self.slideMenuDelegate && [self.slideMenuDelegate respondsToSelector:@selector(rightMenuDidOpen)])
            [self.slideMenuDelegate rightMenuDidOpen];
    }];
}

- (void)closeLeftMenu
{
    [self closeLeftMenuAnimated:YES];
}

- (void)closeLeftMenuAnimated:(BOOL)animated
{
    if (self.slideMenuDelegate && [self.slideMenuDelegate respondsToSelector:@selector(leftMenuWillClose)])
        [self.slideMenuDelegate leftMenuWillClose];
    
    CGRect frame = self.currentActiveNVC.view.frame;
    frame.origin.x = 0;

    [UIView animateWithDuration:animated ? self.closeAnimationDuration : 0 delay:0 options:self.closeAnimationCurve animations:^{
        self.currentActiveNVC.view.frame = frame;
        
        if ([self deepnessForLeftMenu])
        {
            self.leftMenu.view.layer.transform = kMenuTransformScale;
            self.leftMenu.view.layer.opacity = kMenuLayerInitialOpacity;
        }
        
        if (self.statusBarView)
        {
            self.statusBarView.layer.opacity = 1;
        }
        self.darknessView.alpha = 0;
    } completion:^(BOOL finished) {
        
        [self.overlayView removeFromSuperview];
        [self desableGestures];
        self.menuState = AMSlideMenuClosed;
        [self.currentActiveNVC.view addGestureRecognizer:self.panGesture];
        
        if (self.slideMenuDelegate && [self.slideMenuDelegate respondsToSelector:@selector(leftMenuDidClose)])
            [self.slideMenuDelegate leftMenuDidClose];
    }];
    
    dispatch_after(dispatch_time(DISPATCH_TIME_NOW, (int64_t)(self.closeAnimationDuration * NSEC_PER_SEC)), dispatch_get_main_queue(), ^{
        self.leftMenu.view.hidden = YES;
    });
}

- (void)closeRightMenu
{
    [self closeRightMenuAnimated:YES];
}

- (void)closeRightMenuAnimated:(BOOL)animated
{
    if (self.slideMenuDelegate && [self.slideMenuDelegate respondsToSelector:@selector(rightMenuWillClose)])
        [self.slideMenuDelegate rightMenuWillClose];
    
    CGRect frame = self.currentActiveNVC.view.frame;
    frame.origin.x = 0;
    
    [UIView animateWithDuration:animated ? self.closeAnimationDuration : 0 delay:0 options:self.closeAnimationCurve animations:^{
        self.currentActiveNVC.view.frame = frame;

        if ([self deepnessForRightMenu])
        {
            self.rightMenu.view.layer.transform = kMenuTransformScale;
            self.rightMenu.view.layer.opacity = kMenuLayerInitialOpacity;
        }
        
        if (self.statusBarView)
        {
            self.statusBarView.layer.opacity = 1;
        }
        
        self.darknessView.alpha = 0;
    } completion:^(BOOL finished) {
        
        
        [self.overlayView removeFromSuperview];        
        [self desableGestures];
        self.menuState = AMSlideMenuClosed;
        [self.currentActiveNVC.view addGestureRecognizer:self.panGesture];
        
        if (self.slideMenuDelegate && [self.slideMenuDelegate respondsToSelector:@selector(rightMenuDidClose)])
            [self.slideMenuDelegate rightMenuDidClose];
    }];
    dispatch_after(dispatch_time(DISPATCH_TIME_NOW, (int64_t)(self.closeAnimationDuration * NSEC_PER_SEC)), dispatch_get_main_queue(), ^{
        self.leftMenu.view.hidden = YES;
    });
}

- (void)closeMenu
{
    if (self.menuState == AMSlideMenuLeftOpened)
    {
        [self closeLeftMenu];
    }
    else if (self.menuState == AMSlideMenuRightOpened)
    {
        [self closeRightMenu];
    }
}

- (void)switchCurrentActiveControllerToController:(UINavigationController *)nvc fromMenu:(UITableViewController *)menu
{
    self.leftPanDisabled  = NO;
    self.rightPanDisabled = NO;
    
    if (self.isInitialStart)
    {
        if ([self primaryMenu] == AMPrimaryMenuLeft)
        {
            if ([menu isKindOfClass:[AMSlideMenuLeftTableViewController class]])
            {
                self.initialViewController = nvc;
            }
            if ([menu isKindOfClass:[AMSlideMenuRightTableViewController class]] && self.leftMenu)
            {
                if (self.currentActiveNVC)
                {
                    [self.currentActiveNVC.view removeFromSuperview];
                }
                    self.currentActiveNVC = self.initialViewController;
                
                [self.view addSubview:self.currentActiveNVC.view];
                
                return;
            }
        }
        else if ([self primaryMenu] == AMPrimaryMenuRight)
        {
            if ([menu isKindOfClass:[AMSlideMenuLeftTableViewController class]] && self.rightMenu)
            {
                if (self.currentActiveNVC)
                {
                    [self.currentActiveNVC.view removeFromSuperview];
                }
                self.currentActiveNVC = self.initialViewController;
                
                [self.view addSubview:self.currentActiveNVC.view];
                
                return;
            }
            if ([menu isKindOfClass:[AMSlideMenuRightTableViewController class]])
            {
                self.initialViewController = nvc;
            }
        }
    }
    

    if (self.currentActiveNVC)
    {
        [self.currentActiveNVC.view removeFromSuperview];
    }
    self.currentActiveNVC = nvc;
    
    [self.view addSubview:nvc.view];
    [self addChildViewController:nvc];
    [self configureDarknessView];
    
    if (![UIApplication sharedApplication].statusBarHidden)
    {
        // Configuring for iOS 6.x
        if ([[[UIDevice currentDevice] systemVersion] floatValue] < 7.0)
        {
            if (!UIInterfaceOrientationIsLandscape([UIApplication sharedApplication].statusBarOrientation)) {
                CGRect frame = self.currentActiveNVC.view.frame;
                
                if (UI_USER_INTERFACE_IDIOM() != UIUserInterfaceIdiomPad || UIDeviceOrientationIsPortrait(initialOrientation)) {
                    frame.origin.y = -20;
                }
                
                self.currentActiveNVC.view.frame = frame;
            }
        }
        else
        {
            if (self.statusBarView)
            {
                CGRect frame = self.statusBarView.frame;
                if (frame.size.height > 20)
                {
                    frame.size.height = 20;
                }
                frame.origin.y = -1 * frame.size.height;
                self.statusBarView.frame = frame;
                self.statusBarView.layer.opacity = 1;
                [self.statusBarView removeFromSuperview];
                [self.currentActiveNVC.view addSubview:self.statusBarView];
                
                CGRect contentFrame = self.currentActiveNVC.view.frame;
                
                contentFrame.origin.y = frame.size.height;
                contentFrame.size.height = self.view.frame.size.height - frame.size.height;
                
                self.currentActiveNVC.view.frame = contentFrame;
            }
        }
    }
    
    [self closeMenu];
    [self.currentActiveNVC.view addGestureRecognizer:self.panGesture];
    
    if ([menu isKindOfClass:[AMSlideMenuLeftTableViewController class]])
    {
        [self.rightMenu.tableView deselectRowAtIndexPath:[self.rightMenu.tableView indexPathForSelectedRow] animated:NO];
    }
    else if ([menu isKindOfClass:[AMSlideMenuRightTableViewController class]])
    {
        [self.leftMenu.tableView deselectRowAtIndexPath:[self.leftMenu.tableView indexPathForSelectedRow] animated:NO];
    }
}

- (void)openContentViewControllerForMenu:(AMSlideMenu)menu atIndexPath:(NSIndexPath *)indexPath
{
    if (menu == AMSlideMenuLeft)
    {
        if (!self.leftMenu)
            return;
        
        [self.leftMenu.tableView selectRowAtIndexPath:indexPath animated:YES scrollPosition:UITableViewScrollPositionNone];
        
        if ([self respondsToSelector:@selector(navigationControllerForIndexPathInLeftMenu:)]) {
            UINavigationController *navController = [self navigationControllerForIndexPathInLeftMenu:indexPath];
            AMSlideMenuContentSegue *segue = [[AMSlideMenuContentSegue alloc] initWithIdentifier:@"ContentSugue" source:self.leftMenu destination:navController];
            [segue perform];
        } else {
            NSString *identifier = [self segueIdentifierForIndexPathInLeftMenu:indexPath];
            [self.leftMenu performSegueWithIdentifier:identifier sender:self.leftMenu];
        }
    }
    else if (menu == AMSlideMenuRight)
    {
        if (!self.rightMenu)
            return;
        
        [self.rightMenu.tableView selectRowAtIndexPath:indexPath animated:YES scrollPosition:UITableViewScrollPositionNone];
        
        if ([self respondsToSelector:@selector(navigationControllerForIndexPathInRightMenu:)]) {
            UINavigationController *navController = [self navigationControllerForIndexPathInRightMenu:indexPath];
            AMSlideMenuContentSegue *segue = [[AMSlideMenuContentSegue alloc] initWithIdentifier:@"ContentSugue" source:self.rightMenu destination:navController];
            [segue perform];
        } else {
            NSString *identifier = [self segueIdentifierForIndexPathInRightMenu:indexPath];
            [self.rightMenu performSegueWithIdentifier:identifier sender:self.rightMenu];
        }
    }
}

- (void)addGestures
{
    if (self.overlayView)
    {
        [self.overlayView removeFromSuperview];
    }
    else
    {
        self.overlayView = [[UIView alloc] initWithFrame:self.currentActiveNVC.view.bounds];
    }
    
    CGRect frame = self.overlayView.frame;
    frame.size = self.currentActiveNVC.view.bounds.size;
    self.overlayView.frame = frame;
    self.overlayView.layer.zPosition = 10000;
    self.overlayView.backgroundColor = [UIColor clearColor];

    [self.currentActiveNVC.view addSubview:self.overlayView];
    
    [self.overlayView addGestureRecognizer:self.tapGesture];
    [self.overlayView addGestureRecognizer:self.panGesture];
}

- (void)fixStatusBarWithView:(UIView *)view
{
    [self.statusBarView removeFromSuperview];
    
    self.statusBarView = view;
    
    if (![UIApplication sharedApplication].statusBarHidden)
    {
        // Configuring for iOS 6.x
        if ([[[UIDevice currentDevice] systemVersion] floatValue] >= 7.0)
        {
            if (self.statusBarView)
            {
                CGRect frame = self.statusBarView.frame;
                if (frame.size.height > 20)
                {
                    frame.size.height = 20;
                }
                frame.origin.y = -1 * frame.size.height;
                self.statusBarView.frame = frame;
                self.statusBarView.layer.opacity = 1;
                [self.currentActiveNVC.view addSubview:self.statusBarView];
                
                CGRect contentFrame = self.currentActiveNVC.view.frame;
                
                contentFrame.origin.y = frame.size.height;
                contentFrame.size.height = self.view.frame.size.height - frame.size.height;
                
                self.currentActiveNVC.view.frame = contentFrame;
            }
        }
    }
}

- (void)unfixStatusBarView
{
    [self.statusBarView removeFromSuperview];
    self.statusBarView = nil;
}

- (void)enableGestures
{
    self.tapGesture.enabled = YES;
//    self.panGesture.enabled = YES;
}

- (void)desableGestures
{
    self.tapGesture.enabled = NO;
//    self.panGesture.enabled = NO;
}

/*----------------------------------------------------*/
#pragma mark - Gesture Recognizers -
/*----------------------------------------------------*/

- (void)handleTapGesture:(UITapGestureRecognizer *)tap
{
    [self closeMenu];
}

- (void)handlePanGesture:(UIPanGestureRecognizer *)gesture
{
    static CGPoint panStartPosition = (CGPoint){0,0};
    
    if (self.leftPanDisabled && self.rightPanDisabled)
    {
        return;
    }
    
    UIView* panningView = gesture.view;
    if (self.menuState != AMSlideMenuClosed)
    {
        panningView = panningView.superview;
    }
    
    CGPoint translation = [gesture translationInView:panningView];
    
    if ([gesture state] == UIGestureRecognizerStateBegan)
    {
        panStartPosition = [gesture locationInView:panningView];
        panStarted = YES;
    }
    else if ([gesture state] == UIGestureRecognizerStateEnded || [gesture state] == UIGestureRecognizerStateCancelled)
    {
        if (self.menuState != AMSlideMenuClosed)
        {
            if (self.menuState == AMSlideMenuLeftOpened)
            {
                if (panningView.frame.origin.x < ([self leftMenuWidth] / 2.0f))
                {
                    [self closeLeftMenu];
                }
                else
                {
                    [self openLeftMenu];
                }
            }
            else if (self.menuState == AMSlideMenuRightOpened)
            {
                if (self.view.frame.size.width - (panningView.frame.origin.x + panningView.frame.size.width) < ([self rightMenuWidth] / 2.0f))
                {
                    [self closeRightMenu];
                }
                else
                {
                    [self openRightMenu];
                }
            }
        }
        else
        {
            if (panningState == AMSlidePanningStateRight)
            {
                if (panningView.frame.origin.x < ([self leftMenuWidth] / 2.0f))
                {
                    [self closeLeftMenu];
                }
                else
                {
                    [self openLeftMenu];
                }

            }
            if (panningState == AMSlidePanningStateLeft)
            {
                if (self.view.frame.size.width - (panningView.frame.origin.x + panningView.frame.size.width) < ([self rightMenuWidth] / 2.0f))
                {
                    [self closeRightMenu];
                }
                else
                {
                    [self openRightMenu];
                }
            }
        }
        
        panningState = AMSlidePanningStateStopped;
    }
    else
    {
        if (!CGPointEqualToPoint(panStartPosition, (CGPoint){0,0}))
        {
            CGFloat actualWidth = panningView.frame.size.width * ([self panGestureWarkingAreaPercent] / 100.0f);
            if (panStartPosition.x > actualWidth && panStartPosition.x < panningView.frame.size.width - actualWidth && self.menuState == AMSlideMenuClosed)
            {
                return;
            }
        }
        
        //--- Calculate pan position
        if(panStarted)
        {
            panStarted = NO;
            
            if (panningView.frame.origin.x + translation.x < 0)
            {
                panningState = AMSlidePanningStateLeft;

                [self rightMenuWillReveal];
                if (self.menuState == AMSlideMenuClosed)
                {
                    self.leftMenu.view.hidden = YES;
                    self.rightMenu.view.hidden = NO;
                }
            }
            else if(panningView.frame.origin.x + translation.x > 0)
            {
                panningState = AMSlidePanningStateRight;

                [self leftMenuWillReveal];
                if (self.menuState == AMSlideMenuClosed)
                {
                    self.leftMenu.view.hidden = NO;
                    self.rightMenu.view.hidden = YES;
                }
            }
        }
        //----
        
        //----
        if (panningState == AMSlidePanningStateLeft && self.leftPanDisabled)
        {
            panningState = AMSlidePanningStateStopped;
            return;
        }
        else if (panningState == AMSlidePanningStateRight && self.rightPanDisabled)
        {
            panningState = AMSlidePanningStateStopped;
            return;
        }
        //----
        
        if (self.menuState == AMSlideMenuLeftOpened)
        {
            if (abs(translation.x) > kPanMinTranslationX && translation.x < 0)
            {
                [self closeLeftMenu];
            }
            else if ((panningView.frame.origin.x + translation.x) < [self leftMenuWidth] && (panningView.frame.origin.x + translation.x) >= 0)
            {
                [panningView setCenter:CGPointMake([panningView center].x + translation.x, [panningView center].y)];
                
                [self configure3DTransformForMenu:AMSlideMenuLeft panningView:panningView];
            }
        }
        else if (self.menuState == AMSlideMenuRightOpened)
        {
            if (abs(translation.x) > kPanMinTranslationX && translation.x > 0)
            {
                [self closeRightMenu];
            }
            else if (self.view.frame.size.width - (panningView.frame.origin.x + panningView.frame.size.width + translation.x) < [self rightMenuWidth] &&
                panningView.frame.origin.x <= 0)
            {
                [panningView setCenter:CGPointMake([panningView center].x + translation.x, [panningView center].y)];

                [self configure3DTransformForMenu:AMSlideMenuRight panningView:panningView];
            }
        }
        else if (self.menuState == AMSlideMenuClosed)
        {
            if (panningState == AMSlidePanningStateRight && self.leftMenu)
            {
                if (abs(translation.x) > kPanMinTranslationX && translation.x > 0)
                {
                    [self openLeftMenu];
                }
                else if ((panningView.frame.origin.x + translation.x) < [self leftMenuWidth] && (panningView.frame.origin.x + translation.x) > 0)
                {
                    [panningView setCenter:CGPointMake([panningView center].x + translation.x, [panningView center].y)];
                    
                    [self configure3DTransformForMenu:AMSlideMenuLeft panningView:panningView];
                }
            }
            else if (panningState == AMSlidePanningStateLeft  && self.rightMenu)
            {
                if (abs(translation.x) > kPanMinTranslationX && translation.x < 0)
                {
                    [self openRightMenu];
                }
                else if (self.view.frame.size.width - (panningView.frame.origin.x + panningView.frame.size.width + translation.x) <= [self rightMenuWidth])
                {
                    if (panningView.frame.origin.x + translation.x <= 0)
                    {
                        [panningView setCenter:CGPointMake([panningView center].x + translation.x, [panningView center].y)];
                        
                        [self configure3DTransformForMenu:AMSlideMenuRight panningView:panningView];
                    }
                }
            }
        }
    }
    
    if (panningPreviousEventDate != nil) {
        CGFloat movement = panningView.frame.origin.x - panningPreviousPosition;
        NSTimeInterval movementDuration = [[NSDate date] timeIntervalSinceDate:panningPreviousEventDate] * 1000.0f;
        panningXSpeed = movement / movementDuration;
    }
    panningPreviousEventDate = [NSDate date];
    panningPreviousPosition = panningView.frame.origin.x;
    
    [gesture setTranslation:CGPointZero inView:panningView];
}

- (void)configure3DTransformForMenu:(AMSlideMenu)menu panningView:(UIView *)panningView
{
    float cx = 0;
    float cy = 0;
    float cz = 0;
    float opacity = 0;

    /********************************************* DEEPNESS EFFECT *******************************************************/
    if (menu == AMSlideMenuLeft && panningView.frame.origin.x != 0 && [self deepnessForLeftMenu])
    {
        cx = kMenuTransformScale.m11 + (panningView.frame.origin.x / [self leftMenuWidth]) * (1.0 - kMenuTransformScale.m11);
        cy = kMenuTransformScale.m22 + (panningView.frame.origin.x / [self leftMenuWidth]) * (1.0 - kMenuTransformScale.m22);
        cz = kMenuTransformScale.m33 + (panningView.frame.origin.x / [self leftMenuWidth]) * (1.0 - kMenuTransformScale.m33);
        
        opacity = kMenuLayerInitialOpacity + (panningView.frame.origin.x / [self leftMenuWidth]) * (1.0 - kMenuLayerInitialOpacity);
        
        self.leftMenu.view.layer.transform = CATransform3DMakeScale(cx, cy, cz);
        self.leftMenu.view.layer.opacity = opacity;
    }
    else if (menu == AMSlideMenuRight && panningView.frame.origin.x != 0 && [self deepnessForRightMenu])
    {
        cx = kMenuTransformScale.m11 + (-panningView.frame.origin.x / [self rightMenuWidth]) * (1.0 - kMenuTransformScale.m11);
        cy = kMenuTransformScale.m22 + (-panningView.frame.origin.x / [self rightMenuWidth]) * (1.0 - kMenuTransformScale.m22);
        cz = kMenuTransformScale.m33 + (-panningView.frame.origin.x / [self rightMenuWidth]) * (1.0 - kMenuTransformScale.m33);
        
        opacity = kMenuLayerInitialOpacity + (-panningView.frame.origin.x / [self rightMenuWidth]) * (1.0 - kMenuLayerInitialOpacity);
        
        self.rightMenu.view.layer.transform = CATransform3DMakeScale(cx, cy, cz);
        self.rightMenu.view.layer.opacity = opacity;
    }
    /********************************************* DEEPNESS EFFECT *******************************************************/
    
    /********************************************* STATUS BAR FIX *******************************************************/
    if (menu == AMSlideMenuLeft && panningView.frame.origin.x != 0)
    {
        if (self.statusBarView)
        {
            self.statusBarView.layer.opacity = 1 - panningView.frame.origin.x / [self leftMenuWidth];
        }
    }
    else if (menu == AMSlideMenuRight && panningView.frame.origin.x != 0)
    {
        if (self.statusBarView)
        {
            self.statusBarView.layer.opacity = 1 - abs(panningView.frame.origin.x) / [self rightMenuWidth];
        }
    }
    /********************************************* STATUS BAR FIX *******************************************************/
    
    /********************************************* DARKNESS EFFECT *******************************************************/
    if (menu == AMSlideMenuLeft)
    {
        CGFloat alpha = [self maxDarknessWhileLeftMenu] * (panningView.frame.origin.x / [self leftMenuWidth]);

        self.darknessView.alpha = alpha;
    }
    else if(menu == AMSlideMenuRight)
    {
        CGFloat alpha = [self maxDarknessWhileRightMenu] * (abs(panningView.frame.origin.x) / [self rightMenuWidth]);
        
        self.darknessView.alpha = alpha;
    }
    /********************************************* DARKNESS EFFECT *******************************************************/
}
@end
