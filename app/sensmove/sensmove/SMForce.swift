//
//  SMForce.swift
//  app
//
//  Created by RIEUX Alexandre on 13/03/2015.
//  Copyright (c) 2015 RIEUX Alexandre. All rights reserved.
//

import Foundation
import SceneKit

class SMForce: SMSensor {

    /// Dynamic observable variable
    dynamic var currentForcePressure: Float
    
    /// Archived forces
    var archivedForces: [Float]

    let position: SCNVector3
    let size: Int
    
    required init(id: Int, pos: JSON) {

        self.currentForcePressure = 0.0
        self.archivedForces = []
        self.position = SCNVector3(x: pos["x"].floatValue, y: pos["y"].floatValue, z: Float(0))

        self.size = pos["size"].int!
        
        super.init(id: id, creation: NSDate())
    }

    required init(id: Int, creation: NSDate) {
        fatalError("init(id:creation:) has not been implemented")
    }

    /// Update the force pressure on sensor
    func updateForce(force: Float) {
        self.archivedForces.append(self.currentForcePressure)
        self.currentForcePressure = force
    }
    
    func getAverageForce() -> Float {
        return self.archivedForces.reduce(0, combine: { $0 + $1 }) / Float(self.archivedForces.count)
    }
}