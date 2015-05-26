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
    
    private var Pos3D: SCNVector3?
    private var Node: SCNNode?
    
    required init(id:Int, creation:NSDate, pos:SCNVector3) {
        
        self.Pos3D = pos
        super.init(id: id, creation: creation)
        
        self.initializeVisual()
    }

    required init(id: Int, creation: NSDate) {
        fatalError("init(id:creation:) has not been implemented")
    }
    
    func getNode() -> SCNNode {
        return self.Node!
    }
    
    func initializeVisual(){
        var Sphere = SCNSphere(radius: 0.3)
        self.Node = SCNNode(geometry: Sphere)
        self.Node?.position = self.Pos3D!
    }
    
    // Update the z position of pressions measurement
    func updateZ(zPos: Float) {
        self.Pos3D?.z = zPos
    }
}