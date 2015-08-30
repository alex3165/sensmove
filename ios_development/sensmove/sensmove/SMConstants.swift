//
//  SMConstants.swift
//  sensmove
//
//  Created by RIEUX Alexandre on 16/05/2015.
//  Copyright (c) 2015 ___alexprod___. All rights reserved.
//

import Foundation
import UIKit
import CoreBluetooth



let blockDelimiter = "$"
let rightInsoleMarkup = "@"
let leftInsoleMarkup = "*"

let insoleName = "coco"


//MARK: UUID Retrieval

func uartServiceUUID()->CBUUID{
    
    return CBUUID(string: "6e400001-b5a3-f393-e0a9-e50e24dcca9e")
    
}


func txCharacteristicUUID()->CBUUID{
    
    return CBUUID(string: "6e400002-b5a3-f393-e0a9-e50e24dcca9e")
}


func rxCharacteristicUUID()->CBUUID{
    
    return CBUUID(string: "6e400003-b5a3-f393-e0a9-e50e24dcca9e")
}


func deviceInformationServiceUUID()->CBUUID{
    
    return CBUUID(string: "180A")
}


func hardwareRevisionStringUUID()->CBUUID{
    
    return CBUUID(string: "2A27")
}


func manufacturerNameStringUUID()->CBUUID{
    
    return CBUUID(string: "2A29")
}


func modelNumberStringUUID()->CBUUID{
    
    return CBUUID(string: "2A24")
}


func firmwareRevisionStringUUID()->CBUUID{
    
    return CBUUID(string: "2A26")
}


func softwareRevisionStringUUID()->CBUUID{
    
    return CBUUID(string: "2A28")
}


func serialNumberStringUUID()->CBUUID{
    
    return CBUUID(string: "2A25")
}


func systemIDStringUUID()->CBUUID{
    
    return CBUUID(string: "2A23")
}


func dfuServiceUUID()->CBUUID{
    
    return CBUUID(string: "00001530-1212-efde-1523-785feabcd123")
}


func UUIDsAreEqual(firstID:CBUUID, secondID:CBUUID)->Bool {
    return firstID.representativeString() == secondID.representativeString()
}
