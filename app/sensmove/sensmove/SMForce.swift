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

    required init(id: Int, pos: JSON) {
        
        self.currentForcePressure = 0.0
        self.archivedForces = []

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
}