//
//  SMViewController.h
//  POCCharts
//
//  Created by Jean-Sébastien Pélerin on 26/06/2015.
//  Copyright (c) 2015 Sensmove. All rights reserved.
//

#import <UIKit/UIKit.h>
#import "POCCharts-Swift.h"
#import "PNChart.h"
#import "ReactiveCocoa.h"
//#import "SwiftyJSON.h"




@interface SMViewController : UIViewController

@property (nonatomic) PNLineChart * lineChart;
@property (nonatomic) PNCircleChart * circleChart;

@end

