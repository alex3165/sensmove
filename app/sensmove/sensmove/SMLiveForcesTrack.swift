//
//  SMLiveForcesTrack.swift
//  sensmove
//
//  Created by RIEUX Alexandre on 31/05/2015.
//  Copyright (c) 2015 ___alexprod___. All rights reserved.
//

import Foundation
import UIKit
import SceneKit

class SMLiveForcesTrack: SCNScene {
    
    var trackSessionService: SMTrackSessionService?
    var globalNode:  SCNNode = SCNNode()
    
    override init() {
        super.init()
        
        self.trackSessionService = SMTrackSessionService.sharedInstance
        
//        self.createLights()

        if let sole = self.trackSessionService?.currentSession?.rightSole {

//            RACObserve(sole, "forceSensors").subscribeNext({ (forceSensors) -> Void in
//                for sensor: SMForce in sole.forceSensors {
//                    self.globalNode.addChildNode(sensor.getNode())
//                }
//                self.rootNode.addChildNode(self.globalNode)
//
//            })
        }

    }
    
//    func createLights() {
//        var light = SCNLight()
//        var lightNode = SCNNode()
//        
//        light.type = SCNLightTypeOmni
//        light.color = SMColor.orange()
//        lightNode.light = light
//        lightNode.position = SCNVector3(x: -40, y: 40, z: 60)
//        self.rootNode.addChildNode(lightNode)
//        
//        
//        var ambientLight = SCNLight()
//        var ambientLightNode = SCNNode()
//        
//        ambientLight.type = SCNLightTypeAmbient
//        ambientLight.color = SMColor.red()
//        ambientLightNode.light = ambientLight
//        self.rootNode.addChildNode(ambientLightNode)
//        
//    }

    required init(coder aDecoder: NSCoder) {
        fatalError("init(coder:) has not been implemented")
    }
}
