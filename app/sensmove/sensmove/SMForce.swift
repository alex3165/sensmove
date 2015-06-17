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

    private var forceNode: SCNNode?

    required init(id: Int, pos: JSON) {
        super.init(id: id, creation: NSDate())
        self.initializeVisual(pos)
    }

    required init(id: Int, creation: NSDate) {
        fatalError("init(id:creation:) has not been implemented")
    }

    func getNode() -> SCNNode {
        return self.forceNode!
    }

    func initializeVisual(position: JSON){
        var Sphere = SCNSphere(radius: 7.5)
        Sphere.firstMaterial?.diffuse.contents = SMColor.orange()
        self.forceNode = SCNNode(geometry: Sphere)
        self.forceNode?.position.x = position["x"].floatValue
        self.forceNode?.position.y = position["y"].floatValue
        self.forceNode?.position.z = 10
    }

    // Update the z position of pressions measurement
    func updateZ(zPos: Float) {
        self.forceNode?.position.z = zPos
    }
}