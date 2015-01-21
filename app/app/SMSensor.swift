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
    
    private var Pos3D: SCNVector3?
    var id: Int
    private var Node: SCNNode?

    required init(id:Int, pos:SCNVector3) {
        self.id = id
        self.Pos3D = pos
        
        super.init()
        
        self.initializeVisual()
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