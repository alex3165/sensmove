//
//  SMBluetoothSimulator.swift
//  sensmove
//
//  Created by RIEUX Alexandre on 17/06/2015.
//  Copyright (c) 2015 ___alexprod___. All rights reserved.
//

import UIKit

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
