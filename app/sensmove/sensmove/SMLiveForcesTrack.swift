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
    
    override init() {
        super.init()
        
        self.trackSessionService = SMTrackSessionService.sharedInstance
        
        if let sole = self.trackSessionService?.currentSession?.rightSole {

            RACObserve(sole, "forceSensors").subscribeNext({ (forceSensors) -> Void in
                for sensor: SMForce in sole.forceSensors {
                    self.rootNode.addChildNode(sensor.getNode())
                }
            })
        }
    }

    required init(coder aDecoder: NSCoder) {
        fatalError("init(coder:) has not been implemented")
    }
}
