//
//  ViewController.swift
//  POC_PNChart
//
//  Created by Jean-Sébastien Pélerin on 22/06/2015.
//  Copyright (c) 2015 Sensmove. All rights reserved.
//

import UIKit

class ViewController: UIViewController {
    
    override func viewDidLoad() {
        
        super.viewDidLoad()
        
//        Circle Char
        
        
//        PNCircleChart * circleChart = [[PNCircleChart alloc] initWithFrame:CGRectMake(0, 80.0, SCREEN_WIDTH, 100.0) total:[NSNumber numberWithInt:100] current:[NSNumber numberWithInt:60] clockwise:NO shadow:NO];
//        circleChart.backgroundColor = [UIColor clearColor];
//        [circleChart setStrokeColor:PNGreen];
//        [circleChart strokeChart];
        
//        var boundCircle: CGRect = UIScreen.mainScreen().bounds
//        var pnCircle: PNCircleChart = PNCircleChart(frame: CGRect(x: 0, y: 0, width: boundCircle.width, height: boundCircle.height))
//        pnCircle.total = 100
//        pnCircle.current = 60
//        pnCircle.strokeColor = UIColor.redColor()
//        pnCircle.backgroundColor = UIColor.clearColor()
//        pnCircle.strokeChart()
//        
//        self.view.addSubview(pnCircle)
        
        
//       Line char
        
//        var boundLine: CGRect = UIScreen.mainScreen().bounds
//        var pnLine: PNLineChart = PNLineChart(frame: CGRect(x: 0, y: 100, width: boundLine.width, height: boundLine.height))
        
        
// Bar Char
        
        
//        var boundsPNBar: CGRect = UIScreen.mainScreen().bounds
//        var pnBar: PNBarChart = PNBarChart(frame: CGRect(x: 0, y: 135.0, width: boundsPNBar.width, height: boundsPNBar.height))
////        pnBar.setValue(<#value: AnyObject?#>, forKey: <#String#>)
//        pnBar.xLabels = ["FSR1", "FSR2", "FSR3", "FSR4", "FSR5", "FSR6","FSR7"]
//        var dataArray: [NSInteger] = [10,2,6,3,3,2,3,4]
////        pnBar.updateChartData(dataArray)
//
//   pnBar.yValues = dataArray as [NSInteger]
//        pnBar.strokeChart()
//        var bluetoothSimul = SMBluetoothSimulator()
//        
//            self.view.addSubview(pnBar)

        
//  Reactive observer
//        RACObserve(bluetoothSimul, "data").subscribeNext { (next:AnyObject!) -> Void in
//            if let data = next as? NSData {
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
//
//            }
//        }
//        
        
        
        // Do any additional setup after loading the view, typically from a nib.
    }

    override func didReceiveMemoryWarning() {
        super.didReceiveMemoryWarning()
        // Dispose of any resources that can be recreated.
    }


}

