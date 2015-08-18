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

    required init(coder aDecoder: NSCoder) {
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
                var sensorId: Int = sensorForces[index].id
                self.circleChart[sensorId] = self._createChartFromSensor(sensorForces[index])
                self.addSubview(self.circleChart[sensorId] as! PNCircleChart)
            }
        }
    }

    /**
    *
    *   Create chart from SMForce sensor
    *   :param: SMForce The sensor to create circle chart for
    *   :return: PNCircleChart The chart associated to gave sensor
    *
    */
    func _createChartFromSensor(sensor: SMForce) -> PNCircleChart {
        let xPosition = (self.frame.size.width / 2) + CGFloat(sensor.position.x)
        let yPosition = (self.frame.size.height / 2) + CGFloat(sensor.position.y)

        let sensorFrame: CGRect = CGRectMake(xPosition, yPosition, CGFloat(sensor.size), CGFloat(sensor.size))

        let chart: PNCircleChart = PNCircleChart(frame: sensorFrame, total: 1024, current: 0, clockwise: true, shadow: false, shadowColor: SMColor.red(), displayCountingLabel: false, overrideLineWidth: 4)

        chart.strokeChart()
        chart.backgroundColor = UIColor.clearColor()
        chart.strokeColor = SMColor.red()
        
        return chart
    }

    func initializeForceObserver() {
        if let rightSole = self.trackSessionService?.getRightSole() {

            let sensorForces = rightSole.forceSensors
            
            for (var index = 0; index < sensorForces.count; index++) {
                
                // rac observe every changes on currentForcePressure dictionary of each sensors
                sensorForces[index].rac_valuesAndChangesForKeyPath("currentForcePressure", options: nil, observer: sensorForces[index]).subscribeNext({ (obj) -> Void in

                    if let dict: NSDictionary = obj.first() as? NSDictionary {
                        let sensorId: Int = Array(dict.allKeys)[0] as! Int
                        let currentValue: Float = dict[sensorId] as! Float

                        if let chart: PNCircleChart = self.circleChart[sensorId] as? PNCircleChart {

                            // Update chart value of observed sensor
                            chart.updateChartByCurrent(currentValue)
                        }
                    }
                })
                
            }
        }
    }
}
