//
//  SMChronometer.swift
//  sensmove
//
//  Created by RIEUX Alexandre on 23/06/2015.
//  Copyright (c) 2015 ___alexprod___. All rights reserved.
//

import UIKit

protocol SMChronometerDelegate {
    func updateChronometer(newTime: String)
}

class SMChronometer: NSObject {
   
    var startTime: NSTimeInterval?
    var timer: NSTimer?
    var delegate: SMChronometerDelegate! = nil

    override init() {
        super.init()
    }

    /**
    *
    *   Start chronometer with NSTimer which trigger updateTime method every second
    *
    */
    func startChronometer() {
        self.startTime = NSDate.timeIntervalSinceReferenceDate()
        self.timer = NSTimer.scheduledTimerWithTimeInterval(1, target: self, selector: Selector("updateTime"), userInfo: nil, repeats: true)
    }

    /**
    *
    *   Calcul new time and convert to string then pass it to the
    *   track controller through delegated method.
    *
    */
    func updateTime() {
        var currentTime = NSDate.timeIntervalSinceReferenceDate()
        var elapsedTime: NSTimeInterval = currentTime - self.startTime!
        
        let minutes = UInt8(elapsedTime / 60.0)
        
        elapsedTime -= (NSTimeInterval(minutes) * 60)
        
        let seconds = UInt8(elapsedTime)
        
        let strMinutes = String(format: "%02d", minutes)
        let strSeconds = String(format: "%02d", seconds)

        delegate.updateChronometer("\(strMinutes):\(strSeconds)")
    }
}
