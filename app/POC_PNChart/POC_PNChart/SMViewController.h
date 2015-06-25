//
//  SMViewController.h
//  
//
//  Created by Jean-Sébastien Pélerin on 25/06/2015.
//
//

#import <UIKit/UIKit.h>
#import "PNChart.h"
//#import "POC_PNChart-Swift.h"

@interface SMViewController : UIViewController

@property (nonatomic) PNLineChart * lineChart;
@property (nonatomic) PNCircleChart * circleChart;

@property (nonatomic, strong) SMBluetoothSimulator *ble;

@end
