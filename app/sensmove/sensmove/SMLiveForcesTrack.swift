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
    var circleChart: [PNCircleChart]!

    override init(frame: CGRect) {
        super.init(frame: frame)
    }

    required init(coder aDecoder: NSCoder) {
        super.init(coder: aDecoder)
        
        self.trackSessionService = SMTrackSessionService.sharedInstance

    }
    
    func initializeCharts() {
        if let rightSole = self.trackSessionService?.getRightSole() {
            let sensorForces: [SMForce] = rightSole.forceSensors

            for (var index = 0; index < sensorForces.count; index++) {
                self.circleChart.append(self.createChartFromSensor(sensorForces[index]))
                self.addSubview(self.circleChart[index])
            }
        }
    }
    
    func createChartFromSensor(sensor: SMForce) -> PNCircleChart {
        let xPosition = (self.frame.size.width / 2) + CGFloat(sensor.position.x)
        let yPosition = (self.frame.size.height / 2) + CGFloat(sensor.position.y)

        let sensorFrame: CGRect = CGRectMake(xPosition, yPosition, CGFloat(sensor.size), CGFloat(sensor.size))
        
        let chart: PNCircleChart = PNCircleChart(frame: sensorFrame, total: 1024, current: 512, clockwise: true, shadow: false, shadowColor: SMColor.red(), displayCountingLabel: false, overrideLineWidth: 6)

        chart.strokeChart()
        chart.backgroundColor = UIColor.clearColor()
        chart.strokeColor = SMColor.red()
        
        return chart
    }
    
    func initializeForceObserver() {
        if let rightSole = self.trackSessionService?.getRightSole() {
            println("Right sole well initialized")
            let sensorForces = rightSole.forceSensors
            
            // TODO: Run every sensors in order to observe them

            for (var index = 0; index < sensorForces.count; index++) {
                RACObserve(sensorForces[index], "currentForcePressure").subscribeNext({ (forceValue) -> Void in
                    let value: Float = forceValue as! Float
                    //self.circleChart[index].updateChartByCurrent(value)
                })
            }
        }
    }
}
