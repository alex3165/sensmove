//
//  SMBLEPeripheral.swift
//  sensmove
//
//  Created by RIEUX Alexandre on 16/05/2015.
//  Copyright (c) 2015 ___alexprod___. All rights reserved.
//

import Foundation
import CoreBluetooth


class SMBLEPeripheral: NSObject, CBPeripheralDelegate {

    var currentPeripheral: CBPeripheral?
    var peripheralManager: CBPeripheralManager?
    var txCharacteristic: CBCharacteristic?

    init(peripheral: CBPeripheral){ //, delegate:SMBLEPeripheralDelegate
        super.init()
        self.currentPeripheral = peripheral
        self.currentPeripheral!.delegate = self

        self.txCharacteristic = CBMutableCharacteristic(type: txCharacteristicUUID(), properties: CBCharacteristicProperties.Notify, value: nil, permissions: CBAttributePermissions.Readable)
        
        //self.peripheralManager = CBPeripheralManager(delegate: self, queue: nil)
        
//        let activeSignal = RACObserve(SMTrackSessionService.sharedInstance.currentSession, "isActive").subscribeNext { (AnyObject) -> Void in
//            
//        }

        
        
//        let peripheralSet = RACObserve(self, "currentPeripheral").filter { $0 != nil }
//        
//        RACSignal.combineLatest([activeSignal, peripheralSet]).subscribeNext { (AnyObject) -> Void in
//            
//        }
    }

    func peripheralManagerDidUpdateState(peripheral: CBPeripheralManager) {
        
    }
    
    func writeString(string:NSString){

        let data = NSData(bytes: string.UTF8String, length: string.length)

        writeRawData(data)
    }
    
    func writeRawData(data:NSData) {
        
        var writeType:CBCharacteristicWriteType = CBCharacteristicWriteType.WithoutResponse
        
        //send data in lengths of <= 20 bytes
        let dataLength = data.length
        let limit = 20
        
        //Below limit, send as-is
        if dataLength <= limit {
            currentPeripheral!.writeValue(data, forCharacteristic: txCharacteristic, type: writeType)
        }
            
            //Above limit, send in lengths <= 20 bytes
        else {
            
            var len = limit
            var loc = 0
            var idx = 0 //for debug
            
            while loc < dataLength {
                
                var rmdr = dataLength - loc
                if rmdr <= len {
                    len = rmdr
                }
                
                let range = NSMakeRange(loc, len)
                var newBytes = [UInt8](count: len, repeatedValue: 0)
                data.getBytes(&newBytes, range: range)
                let newData = NSData(bytes: newBytes, length: len)
                //                    println("\(self.classForCoder.description()) writeRawData : packet_\(idx) : \(newData.hexRepresentationWithSpaces(true))")
                self.currentPeripheral!.writeValue(newData, forCharacteristic: self.txCharacteristic, type: writeType)
                
                loc += len
                idx += 1
            }
        }
    }

}