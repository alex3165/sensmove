//
//  SMForce.swift
//  app
//
//  Created by RIEUX Alexandre on 13/03/2015.
//  Copyright (c) 2015 RIEUX Alexandre. All rights reserved.
//

import Foundation
import SceneKit

let SMForcePressureNewValue = "kSMForcePressureNewValue"

class SMForce: SMSensor {

    // Observable variable, trigger signal when dictionary change
    var currentForcePressure: Float = Float(0)
    
    /// Archived forces
    var archivedForces: [Float]

    let position: SCNVector3
    let size: Int
    
    required init(id: Int, pos: JSON) {

        self.archivedForces = []
        self.position = SCNVector3(x: pos["x"].floatValue, y: pos["y"].floatValue, z: Float(0))

        self.size = pos["size"].int!
        
        super.init(id: id, creation: NSDate())
    }

    required init(id: Int, creation: NSDate) {
        fatalError("init(id:creation:) has not been implemented")
    }

    // Archive previous force pressure and update the current variable mapped with sensor id
    func updateForce(force: Float) {
        self.archivedForces.append(self.currentForcePressure)
        self.currentForcePressure = force
        
        // TMPWORKAROUND
        NSNotificationCenter.defaultCenter().postNotificationName(SMForcePressureNewValue, object: self, userInfo: [
            "sensorId": super.id,
            "value": self.currentForcePressure
            ])
    }

    // Calculate average force by adding archive force array and dividing it by the length
    func getAverageForce() -> Float {
        
        let average: Float = self.archivedForces.reduce(0, combine: { $0 + $1 }) / Float(self.archivedForces.count)

        return average.isNaN ? Float(0) : average
    }
}