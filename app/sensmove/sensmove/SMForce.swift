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

    dynamic var forcePressure: Float
    private var forceNode: SCNNode?

    required init(id: Int, pos: JSON) {
        
        self.forcePressure = 0.0

        super.init(id: id, creation: NSDate())
        
        // self.initializeVisual(pos)
    }

    required init(id: Int, creation: NSDate) {
        fatalError("init(id:creation:) has not been implemented")
    }

    // Update the force pressure on sensor
    func updateForce(force: Float) {
        self.forcePressure = force
        
        // self.updateZ(self.forcePressure)
    }
    
    // Get the current node with it's state
    func getNode() -> SCNNode {
        return self.forceNode!
    }

    func initializeVisual(position: JSON){
        var Sphere = SCNSphere(radius: 7.5)
        Sphere.firstMaterial?.diffuse.contents = SMColor.orange()
        self.forceNode = SCNNode(geometry: Sphere)
        self.forceNode?.position.x = position["x"].floatValue
        self.forceNode?.position.y = position["y"].floatValue
        self.forceNode?.position.z = self.forcePressure
    }

    // Update the z position of pressions measurement
    func updateZ(zPos: Float) {
        self.forceNode?.position.z = zPos
    }
}