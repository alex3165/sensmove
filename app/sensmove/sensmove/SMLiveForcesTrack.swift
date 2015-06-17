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
        
        self.createLights()
//        var cameraNode = SCNNode()
//        cameraNode.camera = SCNCamera()
//        //                cameraNode.camera?.xFov = 303.181519
//        cameraNode.position = SCNVector3Make(0, 0, 25)
////        cameraNode.rotation = SCNVector4(x: -0.995538, y: 0.075670, z: -0.056372, w: 2.034063)
////        cameraNode.scale = SCNVector3(x: 1.000000, y: 1.000000, z: 1.000000)
//        self.rootNode.addChildNode(cameraNode)
        
//        var sphere = SCNSphere(radius: 5)
//        var sphereNode = SCNNode(geometry: sphere)
//        self.rootNode.addChildNode(sphereNode)

//        var cameraNode = SCNNode()
//        cameraNode.camera = SCNCamera()
//        cameraNode.position = SCNVector3Make(0, 0, 100)
//        self.rootNode.addChildNode(cameraNode)

        if let sole = self.trackSessionService?.currentSession?.rightSole {

            RACObserve(sole, "forceSensors").subscribeNext({ (forceSensors) -> Void in
                for sensor: SMForce in sole.forceSensors {
                    self.globalNode.addChildNode(sensor.getNode())
                }
                self.rootNode.addChildNode(self.globalNode)

            })
        }
        //        Optional(<SCNNode: 0x12de1f000 'kSCNFreeViewCameraName' pos(303.181519 419.555328 -121.379990) rot(-0.995538 0.075670 -0.056372 2.034063) scale(1.000000 1.000000 1.000000) | camera=<SCNCamera: 0x1741783c0 'kSCNFreeViewCameraNameCamera'> | no child>)

    }
    
    func createLights() {
        var light = SCNLight()
        var lightNode = SCNNode()
        
        light.type = SCNLightTypeOmni
        light.color = SMColor.orange()
        lightNode.light = light
        lightNode.position = SCNVector3(x: -40, y: 40, z: 60)
        self.rootNode.addChildNode(lightNode)
        
        
        var ambientLight = SCNLight()
        var ambientLightNode = SCNNode()
        
        ambientLight.type = SCNLightTypeAmbient
        ambientLight.color = SMColor.red()
        ambientLightNode.light = ambientLight
        self.rootNode.addChildNode(ambientLightNode)
        
    }

    required init(coder aDecoder: NSCoder) {
        fatalError("init(coder:) has not been implemented")
    }
}
