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

    init(peripheral: CBPeripheral) {
        super.init()
        self.currentPeripheral = peripheral
        self.currentPeripheral!.delegate = self

        self.txCharacteristic = CBMutableCharacteristic(type: txCharacteristicUUID(), properties: CBCharacteristicProperties.Notify, value: nil, permissions: CBAttributePermissions.Readable)
    }
    
    /// Shorthand method that convert string into data and call writeRawData
    func writeString(string:NSString){

        printLog(string, "writeString", "Write data to sensmove sole")
        
        let data = NSData(bytes: string.UTF8String, length: string.length)

        if self.currentPeripheral != nil {
            self.writeRawData(data)
        } else {
            printLog(self.currentPeripheral!, "writeString", "No current peripheral set")
        }
    }
    
    /// Send datas over bluetooth (write on characteristic)
    private func writeRawData(data:NSData) {
        
        var writeType:CBCharacteristicWriteType = CBCharacteristicWriteType.WithoutResponse
        
        /// send data in lengths of <= 20 bytes
        let dataLength = data.length
        let limit = 20
        
        /// Below limit, send as-is
        if dataLength <= limit {
            self.currentPeripheral!.writeValue(data, forCharacteristic: txCharacteristic, type: writeType)
        }
            
        /// Above limit, send in lengths <= 20 bytes
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