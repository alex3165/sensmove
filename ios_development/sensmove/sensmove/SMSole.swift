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
            forceSensors.append(SMForce(id: index++, pos: vector))
        }
    }

    /**
    *   Update each sensors by adding a new value
    */
    func updateEveryForceSensors(forceArray: [JSON]) {
        for(var index = 0; index < forceArray.count; index++) {
            self.forceSensors[index].updateForce(forceArray[index].floatValue)
        }
    }

    
    /**
    *   Get insole average force by doing an average of the average of each force sensors
    */
    func getTotalAverageForce() -> Float {
        var totalForce: Float = Float(0);
        
        for (var i = 0; i < self.forceSensors.count; i++) {
            totalForce += self.forceSensors[i].getAverageForce()
        }

        return totalForce / Float(self.forceSensors.count)
    }

    /// TODO: Store forces and accelerometer sensors in SQLite database
    func toPropertyList() -> NSDictionary {
        var sole: NSDictionary = [
            "id": self.id,
            "isRight": self.isRight
        ]

        return sole
    }
}
