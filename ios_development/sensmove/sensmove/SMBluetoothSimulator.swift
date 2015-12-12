//
//  SMBluetoothSimulator.swift
//  sensmove
//
//  Created by RIEUX Alexandre on 17/06/2015.
//  Copyright (c) 2015 ___alexprod___. All rights reserved.
//

import UIKit

class SMBluetoothSimulator: NSObject {
    
    /// Observable data for RACObserve
    dynamic var data: NSData?
    
    override init() {
        super.init()
        
        /// Timer that call startSimulator every 5 seconds
        NSTimer.scheduledTimerWithTimeInterval(5, target: self, selector: Selector("startSimulator"), userInfo: nil, repeats: true)
    }
    
    /**
    *   Create datas and store it into data variable
    */
    func startSimulator() {
        let jsonData: JSON = [
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

        self.data = try! jsonData.rawData()
    }
}
