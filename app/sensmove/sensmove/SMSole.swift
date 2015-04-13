//
//  SMSole.swift
//  sensmove
//
//  Created by RIEUX Alexandre on 13/04/2015.
//  Copyright (c) 2015 ___alexprod___. All rights reserved.
//

import Foundation

class SMSole {
    
    var isRight: Bool

    var forceSensors: [SMForce] = []
    var accelerometerSensors: [SMAccelerometer] = []
    
    init(isRight: Bool) {
        self.isRight = isRight
    }
}