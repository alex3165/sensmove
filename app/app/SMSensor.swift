//
//  SMSensor.swift
//  app
//
//  Created by Jean-Sébastien Pélerin on 18/11/2014.
//  Copyright (c) 2014 RIEUX Alexandre. All rights reserved.
//

import Foundation
import SceneKit

class SMSensor: NSObject {
    
    var Pos3D: SCNVector3?
    var id: Int
    let radius: Float = 10
    var Sphere: SCNSphere?

    required init(id:Int, pos:SCNVector3) {
        self.id = id
        self.Pos3D = pos
    }
    
    // Update the z position of pressions measurement
    func updateZ(zPos: Float) {
        self.Pos3D?.z = zPos
    }

}