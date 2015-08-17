//
//  ViewController.swift
//  SMPOCChart
//
//  Created by Jean-Sébastien Pélerin on 26/06/2015.
//  Copyright (c) 2015 Sensmove. All rights reserved.
//

import UIKit

class ViewController: UIViewController {
    
    var circleChart: PNCircleChart!
//  var circleCharts: [PNCircleChart]!
    var lineChart: PNLineChart!
    var currentJSON: JSON!

  
    override func viewDidLoad() {
        
        super.viewDidLoad()
        
//        Initialize Bluetooth Simulator
        var bluetoothData : SMBluetoothSimulator = SMBluetoothSimulator()
        bluetoothData.startSimulator()
        

        
//        LineChart
        
        self.lineChart = PNLineChart(frame: CGRectMake(0, 135.0, 400, 200.0))
        self.lineChart.xLabels = ["","","","","","",""]
        self.lineChart.showLabel = true
        self.lineChart.xLabelWidth = 200
        //      self.lineChart.yLabelFormat = "%1.1f"
        self.lineChart.showCoordinateAxis = true
        self.lineChart.showGenYLabels = false
        //lineChart.delegate = self

        var data01: PNLineChartData = PNLineChartData()
        var data01Array: [CGFloat] = [60.1, 160.1, 126.4, 262.2, 186.2, 127.2, 176.2]
        data01.color = UIColor.redColor();
        data01.itemCount = UInt(data01Array.count)
        
        
//        Circles
        
        var boundCircle: CGRect = UIScreen.mainScreen().bounds
        self.circleChart = PNCircleChart(frame: CGRectMake(50, 335, 50, 50), total: 1024, current: 0, clockwise: true, shadow: false, shadowColor: UIColor.redColor(), displayCountingLabel: false, overrideLineWidth: 6)
//        self.circleChart.total = 1024
//        self.circleChart.current = 0
        self.circleChart.strokeColor = UIColor.redColor()
        self.circleChart.backgroundColor = UIColor.clearColor()
        self.circleChart.strokeChart()
        
//        _TODO Replace data01Array by array within currentJSON
        
//       Update data
        var indexdata01 : Int = 0

        data01.getData = { LCLineChartDataGetter in
            var yValue:CGFloat = data01Array[indexdata01]// _TODO : indexdata01, dirty method to iterate
            var item = PNLineChartDataItem(y: yValue)
            indexdata01 = (indexdata01 + 1) % data01Array.count
            return item
        }
        lineChart.chartData = [data01]
        lineChart.strokeChart()

        
//      Observable
        
        RACObserve(bluetoothData, "data").subscribeNext { (next:AnyObject!) -> Void in
            
            if let data = next as? NSData {
                self.currentJSON = JSON(data: data)
                var fsrArray :[JSON] = self.currentJSON["fsr"].arrayValue
                
                for var i = 0; i < fsrArray.count-1 ; i++ {
                    data01Array[i] = CGFloat(fsrArray[i].number!)
                }
                println(data01Array)
                self.testUpdate(data01Array)
                self.lineChart.updateChartData([data01])// _TODO : should be in testUpdate func

//                testUpdate()
//                data01Array = self.currentJSON["fsr"].arrayValue
                //  self.didReceiveDatasFromBle(data)
            }
        }
        
//         Do any additional setup after loading the view, typically from a nib.

        self.view.addSubview(self.lineChart)
        self.view.addSubview(self.circleChart)

    }

    override func didReceiveMemoryWarning() {
        super.didReceiveMemoryWarning()
        // Dispose of any resources that can be recreated.
    }
    
    func testUpdate(dataArray: [CGFloat]){
        self.circleChart.current = dataArray[0]
        println(self.circleChart.current)
        self.circleChart.updateChartByCurrent(dataArray[0])
    }


}

