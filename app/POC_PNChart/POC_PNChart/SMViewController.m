//
//  SMViewController.m
//  
//
//  Created by Jean-Sébastien Pélerin on 25/06/2015.
//
//

#import "SMViewController.h"


@implementation SMViewController

- (void)viewDidLoad
{
    [super viewDidLoad];
    // Do any additional setup after loading the view, typically from a nib.
//    PNLineChart * lineChart = [[PNLineChart alloc] initWithFrame:CGRectMake(0, 135.0, SCREEN_WIDTH, 200.0)];
//    [lineChart setXLabels:@[@"1",@"2",@"3",@"4",@"5"]];
//    // Line Chart No.1
//    NSArray * data01Array = @[@60.1, @160.1, @126.4, @262.2, @186.2];
//    PNLineChartData *data01 = [PNLineChartData new];
//    data01.color = PNFreshGreen;
//    data01.itemCount = lineChart.xLabels.count;
//    data01.getData = ^(NSUInteger index) {
//        CGFloat yValue = [data01Array[index] floatValue];
//        return [PNLineChartDataItem dataItemWithY:yValue];
//    };
//    lineChart.chartData = @[data01];
//    [lineChart strokeChart];
//    [[self view] addSubview:(lineChart)];
    
    

    
//CircleChart

    
    
    self.circleChart = [[PNCircleChart alloc] initWithFrame:CGRectMake(0,100.0, SCREEN_WIDTH, 100.0)
                                                      total:@100
                                                    current:@90
                                                  clockwise:YES];
    
//    [self.circleChart set
   
    self.circleChart.backgroundColor = [UIColor clearColor];
    
    [self.circleChart setStrokeColor:[UIColor clearColor]];
    [self.circleChart setStrokeColorGradientStart:[UIColor redColor]];
    [self.circleChart strokeChart];
    
    [self.view addSubview:self.circleChart];

}

- (void)viewDidUnload
{
//    [self setMessage:nil];
    [super viewDidUnload];
    // Release any retained subviews of the main view.
    // e.g. self.myOutlet = nil;
}

- (void)viewWillAppear:(BOOL)animated
{
    [super viewWillAppear:animated];
}

- (void)viewDidAppear:(BOOL)animated
{
    [super viewDidAppear:animated];
}

- (void)viewWillDisappear:(BOOL)animated
{
    [super viewWillDisappear:animated];
}

- (void)viewDidDisappear:(BOOL)animated
{
    [super viewDidDisappear:animated];
}


@end
