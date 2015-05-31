//
//  SMLiveForcesTrack.swift
//  sensmove
//
//  Created by RIEUX Alexandre on 31/05/2015.
//  Copyright (c) 2015 ___alexprod___. All rights reserved.
//

import UIKit
import SceneKit

class SMLiveForcesTrack: SCNScene {

    override init() {
        let sphereGeometry = SCNSphere(radius: 1.0)
        let sphereNode = SCNNode(geometry: sphereGeometry)

        super.init()

        self.rootNode.addChildNode(sphereNode)
    }

    required init(coder aDecoder: NSCoder) {
        fatalError("init(coder:) has not been implemented")
    }

}
