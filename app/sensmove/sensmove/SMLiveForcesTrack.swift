//
//  SMLiveForcesTrack.swift
//  sensmove
//
//  Created by RIEUX Alexandre on 31/05/2015.
//  Copyright (c) 2015 ___alexprod___. All rights reserved.
//

import Foundation
import UIKit

class SMLiveForcesTrack: UIView {
    
    var trackSessionService: SMTrackSessionService?
    var circleChart: PNCircleChart!

    override init(frame: CGRect) {
        super.init(frame: frame)
    }

    required init(coder aDecoder: NSCoder) {
        super.init(coder: aDecoder)
        
        self.trackSessionService = SMTrackSessionService.sharedInstance
        
        let halfViewWidth: CGFloat = self.frame.size.width / 2
        let halfViewHeight: CGFloat = self.frame.size.height / 2
        
        self.circleChart = PNCircleChart(frame: CGRectMake(halfViewWidth, halfViewHeight, 50, 50), total: 1024, current: 0, clockwise: true, shadow: false, shadowColor: UIColor.redColor(), displayCountingLabel: false, overrideLineWidth: 6)
        
        self.circleChart.strokeColor = UIColor.redColor()
        self.circleChart.backgroundColor = UIColor.clearColor()
        self.circleChart.strokeChart()

        self.addSubview(self.circleChart)
    }
    
    func initializeForceObserver() {
        if let rightSole = self.trackSessionService?.getRightSole() {
            println("Right sole well initialized")
            let sensorForces = rightSole.forceSensors
            
            // TODO: Run every sensors in order to observe them

            //            for forceSensor in sensorForces {
            RACObserve(sensorForces[0], "currentForcePressure").subscribeNext({ (forceValue) -> Void in
                let value: Float = forceValue as! Float
                printLog(value, "Force Value", "\(value)")
                self.circleChart.current = value
                self.circleChart.updateChartByCurrent(value)
            })
            //            }
        }
    }
}
