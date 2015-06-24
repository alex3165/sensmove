//
//  SMBluetoothSimulator.swift
//  POC_PNChart
//
//  Created by Jean-Sébastien Pélerin on 22/06/2015.
//  Copyright (c) 2015 Sensmove. All rights reserved.
//

import Foundation

class SMBluetoothSimulator: NSObject {
    
    var timer: NSTimer?
    dynamic var data: NSData?
    
    override init() {
        super.init()
        
        self.timer = NSTimer.scheduledTimerWithTimeInterval(5, target: self, selector: Selector("startSimulator"), userInfo: nil, repeats: true)
    }
    
    func startSimulator() {
        var jsonData: JSON = [
            "fsr": [
                Int(arc4random_uniform(1024)),
                Int(arc4random_uniform(1024)),
                Int(arc4random_uniform(1024)),
                Int(arc4random_uniform(1024)),
                Int(arc4random_uniform(1024)),
                Int(arc4random_uniform(1024)),
                Int(arc4random_uniform(1024))
            ],
            "acc": [
                Int(arc4random_uniform(255)),
                Int(arc4random_uniform(255)),
                Int(arc4random_uniform(255))
            ]
        ];
        
        self.data = jsonData.rawData()!
    }
}