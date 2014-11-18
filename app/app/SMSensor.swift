//
//  SMSensor.swift
//  app
//
//  Created by Jean-Sébastien Pélerin on 18/11/2014.
//  Copyright (c) 2014 RIEUX Alexandre. All rights reserved.
//

import Foundation

struct Vector3D {
    var x = 0.0
    var y = 0.0
    var z = 0.0
}

class SMSensor: NSObject {
    
 
    var id: Int
    var pos: Vector3D?
    let radius: Float = 10

    required init(id:Int, pos:Vector3D) {
        
        self.id = id
        self.pos = pos
    }

}