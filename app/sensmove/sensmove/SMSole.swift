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
    var accelerometerSensors: SMAccelerometer?
    
    let id: NSString

    /**
    *
    *   Initialize new sole object
    *   :param:  simpleVectors  x / y positions of vectors
    *   :param:  isRight  Boolean that specify if the sole is right or left
    */
    init(simpleVectors: JSON, isRight: Bool) {

        /// TODO: Get ID from sole at bluetooth initialization
        self.id = "SLPSENSMOVE01"
        
        self.isRight = isRight
        super.init()
        
        self.createForceSensorsFromVectors(simpleVectors)
        self.createAccSensors()
    }

    func createAccSensors() {
        self.accelerometerSensors = SMAccelerometer(id: 0)
    }

    /**
    *
    *   Create force sensors from JSON object
    *   :param:  forceVectors  JSON object
    *
    */
    func createForceSensorsFromVectors(forceVectors: JSON) {

        var index: Int = forceVectors.count - 1

        for (key: String, vector: JSON) in forceVectors {
            forceSensors.append(SMForce(id: index--, pos: vector))
        }
    }

    /// TODO: Add forces and acc sensors in sqlite database
    func toPropertyList() -> NSDictionary {
        var sole: NSDictionary = [
            "id": self.id,
            "isRight": self.isRight
        ]

        return sole
    }
}
