//
//  SMBLEInfor.swift
//  sensmove
//
//  Created by RIEUX Alexandre on 16/05/2015.
//  Copyright (c) 2015 ___alexprod___. All rights reserved.
//

import Foundation

public struct SMBLEDescriptor {
    
    var title:String!
    var UUID:NSUUID!
    
}


public struct SMBLECharacteristic {
    
    var title:String!
    var UUID:NSUUID!
    var descriptors:[SMBLEDescriptor]
    
}


public struct SMBLEService {
    
    var title:String!
    var UUID:NSUUID!
    var characteristics:[SMBLECharacteristic]
    
}