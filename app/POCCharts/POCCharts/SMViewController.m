//
//  SMViewController.m
//  POCCharts
//
//  Created by Jean-Sébastien Pélerin on 26/06/2015.
//  Copyright (c) 2015 Sensmove. All rights reserved.
//

#import "SMViewController.h"
//#import "SwiftyJSON.h"

@interface SMViewController ()

@end

@implementation SMViewController

- (void)viewDidLoad {
    [super viewDidLoad];
    // Do any additional setup after loading the view.
    
    //Bluetooth low energy Simulator
    SMBluetoothSimulator * bluetoothData = [[SMBluetoothSimulator alloc] init];
    [bluetoothData startSimulator];
    
    //JSON data
//    NSData *jsonData = [bluetoothData data];
    
//    if let data = next as? NSData {
        //                var jsonData: JSON = JSON(data: data)
        //                var fsr: Array<JSON> = jsonData["fsr"].arrayValue
        //                var arrayPN: Array<Float> = []
        ////                pn.yValues = []
        //                for value in fsr {
        //                    arrayPN += [value.floatValue]
        //
        ////                    pn.yValues.append(value.intValue)
        //                }
        //                pnBar.yValues = arrayPN
        //                pnBar.strokeChart()

 
    
    //For Line Chart
    _lineChart = [[PNLineChart alloc] initWithFrame:CGRectMake(0, 135.0, SCREEN_WIDTH, 200.0)];
    [_lineChart setXLabels:@[@"SEP 1",@"SEP 2",@"SEP 3",@"SEP 4",@"SEP 5"]];
    
    // Line Chart No.1
    NSArray * data01Array = @[@60.1, @160.1, @126.4, @262.2, @186.2];
    PNLineChartData *data01 = [PNLineChartData new];
    data01.color = PNRed;
    data01.itemCount = _lineChart.xLabels.count;
    data01.getData = ^(NSUInteger index) {
        CGFloat yValue = [data01Array[index] floatValue];
        return [PNLineChartDataItem dataItemWithY:yValue];
    };
    
    _lineChart.chartData = @[data01];
    [_lineChart strokeChart];
    
//    ReactiveCocoa : observing when the bluetooth data is changing
    [RACObserve(bluetoothData, data) subscribeNext:^(NSObject *next) {
        NSLog(@"IN RAC WE ARE");
//        JSON *hello
        //        if(jsonData ){
        //
        //        }
        //        NSLog(@"%@", newName);
    }];
    
    [self.view addSubview:_lineChart];
}

- (void)didReceiveMemoryWarning {
    [super didReceiveMemoryWarning];
    // Dispose of any resources that can be recreated.
}



/*
#pragma mark - Navigation

// In a storyboard-based application, you will often want to do a little preparation before navigation
- (void)prepareForSegue:(UIStoryboardSegue *)segue sender:(id)sender {
    // Get the new view controller using [segue destinationViewController].
    // Pass the selected object to the new view controller.
}
*/

@end
