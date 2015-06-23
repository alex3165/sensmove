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

    var forceSensors: [SMForce] = []
    var accelerometerSensors: [SMAccelerometer] = []

    /**
    *
    *   Initialize new sole object
    *   :param:  simpleVectors  x / y positions of vectors
    *   :param:  isRight  Boolean that specify if the sole is right or left
    */
    init(simpleVectors: JSON, isRight: Bool) {

        self.isRight = isRight
        super.init()
        
        self.createSensortsFromVectors(simpleVectors)
    }
    
    /**
    *
    *   Create force sensors from JSON object
    *   :param:  forceVectors  JSON object
    *
    */
    func createSensortsFromVectors(forceVectors: JSON) {

        var index: Int = forceVectors.count - 1

        for (key: String, vector: JSON) in forceVectors {
            forceSensors.append(SMForce(id: index--, pos: vector))
        }
    }
}
