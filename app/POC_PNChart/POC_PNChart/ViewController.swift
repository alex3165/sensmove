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
        var bluetoothSimul = SMBluetoothSimulator()
        var bounds: CGRect = UIScreen.mainScreen().bounds
        var pn: PNBarChart = PNBarChart(frame: CGRect(x: 0, y: 135.0, width: bounds.width, height: bounds.height))
        pn.xLabels = ["SEP 1", "SEP 2", "SEP3", "SEP 4", "SEP 5"]
        pn.yValues = [1,10,2,6,3]
        pn.strokeChart()
        RACObserve(bluetoothSimul,"data")
        
        self.view.addSubview(pn)
        // Do any additional setup after loading the view, typically from a nib.
    }

    override func didReceiveMemoryWarning() {
        super.didReceiveMemoryWarning()
        // Dispose of any resources that can be recreated.
    }


}

