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
    var circleChart: NSMutableDictionary!

    override init(frame: CGRect) {
        super.init(frame: frame)
    }

    required init?(coder aDecoder: NSCoder) {
        super.init(coder: aDecoder)
        
        self.trackSessionService = SMTrackSessionService.sharedInstance

        self.circleChart = NSMutableDictionary()
    }

    /**
    *   Initialize circle chart elements and append them to SMLiveForcesTrack view
    */
    func initializeCharts() {
        if let rightSole = self.trackSessionService?.getRightSole() {
            let sensorForces: [SMForce] = rightSole.forceSensors

            for (var index = 0; index < sensorForces.count; index++) {
                let sensorId: Int = sensorForces[index].id
                self.circleChart[sensorId] = self._createChartFromSensor(sensorForces[index])
                self.addSubview(self.circleChart[sensorId] as! PNCircleChart)
            }
        }
    }

    /**
    *
    *   Create chart from SMForce sensor
    *   - parameter SMForce: The sensor to create circle chart for
    *   :return: PNCircleChart The chart associated to gave sensor
    *
    */
    func _createChartFromSensor(sensor: SMForce) -> PNCircleChart {
        let xPosition = (self.frame.size.width / 2) + CGFloat(sensor.position.x)
        let yPosition = (self.frame.size.height / 2) + CGFloat(sensor.position.y)

        let sensorFrame: CGRect = CGRectMake(xPosition, yPosition, CGFloat(sensor.size), CGFloat(sensor.size))

        let chart: PNCircleChart = PNCircleChart(frame: sensorFrame, total: 1024, current: 0, clockwise: true, shadow: true, shadowColor: SMColor.ligthGrey(), displayCountingLabel: false, overrideLineWidth: 4)

        
        chart.backgroundColor = UIColor.clearColor()
        chart.strokeColor = SMColor.red()
        chart.strokeChart()

        return chart
    }

    func initializeForceObserver() {
        NSNotificationCenter.defaultCenter().addObserver(self, selector: Selector("connectionChanged:"), name: SMForcePressureNewValue, object: nil)
    }
    
    
    func connectionChanged(notification: NSNotification) {
        let userInfo = notification.userInfo! as NSDictionary
        let sensorId: Int = userInfo["sensorId"] as! Int
        let sensorValue: Float = userInfo["value"] as! Float

        self.circleChart[sensorId]!.updateChartByCurrent(sensorValue)
    }
}
