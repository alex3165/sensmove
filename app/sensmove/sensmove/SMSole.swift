//
//  SMSole.swift
//  sensmove
//
//  Created by RIEUX Alexandre on 13/04/2015.
//  Copyright (c) 2015 ___alexprod___. All rights reserved.
//

import Foundation

class SMSole: NSObject {
    
    let isRight: Bool

    dynamic var forceSensors: [SMForce] = []
    dynamic var accelerometerSensors: [SMAccelerometer] = []

    init(simpleVectors: JSON, isRight: Bool) {

        self.isRight = isRight
        super.init()
        
        self.initializeSensors(simpleVectors)
    }
    
    func initializeSensors(forceVectors: JSON) {

        var index: Int = 0

        for (key: String, vector: JSON) in forceVectors {
            forceSensors.append(SMForce(id: index++, pos: vector))
        }
    }
}
