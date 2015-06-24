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
        
        var bounds: CGRect = UIScreen.mainScreen().bounds
        var pn: PNBarChart = PNBarChart(frame: CGRect(x: 0, y: 135.0, width: bounds.width, height: bounds.height))
        pn.xLabels = ["FSR1", "FSR2", "FSR3", "FSR4", "FSR5", "FSR6","FSR7"]
        pn.yValues = [10,2,6,3,3,2,3,4]
        pn.strokeChart()

        var bluetoothSimul = SMBluetoothSimulator()
        RACObserve(bluetoothSimul, "data").subscribeNext { (next:AnyObject!) -> Void in
            if let data = next as? NSData {
                var jsonData: JSON = JSON(data: data)
                var fsr: Array<JSON> = jsonData["fsr"].arrayValue
                var arrayPN: Array<Float> = []
//                pn.yValues = []
                for value in fsr {
                    arrayPN += [value.floatValue]

//                    pn.yValues.append(value.intValue)
                }
                pn.yValues = arrayPN
                pn.strokeChart()

            }
        }

        
        self.view.addSubview(pn)
        // Do any additional setup after loading the view, typically from a nib.
    }

    override func didReceiveMemoryWarning() {
        super.didReceiveMemoryWarning()
        // Dispose of any resources that can be recreated.
    }


}

