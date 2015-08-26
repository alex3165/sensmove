//
//  SMBLEPeripheral.swift
//  sensmove
//
//  Created by RIEUX Alexandre on 16/05/2015.
//  Copyright (c) 2015 ___alexprod___. All rights reserved.
//

import Foundation
import CoreBluetooth


class SMBLEService: NSObject, CBPeripheralDelegate {
    
    var currentPeripheral: CBPeripheral!
    var txCharacteristic: CBCharacteristic?


    /// Set whenever string is built
    dynamic var blockDataCompleted: NSData!
    dynamic var isConnectedToDevice: Bool = true
    dynamic var isReceivingDatas: Bool = false
    
    let bluetoothBuffer: SMBluetoothDatasBuffer = SMBluetoothDatasBuffer()
    
    init(initWithPeripheral peripheral: CBPeripheral) {
        super.init()
        self.currentPeripheral = peripheral
        self.currentPeripheral.delegate = self

//        self.btCurrentState = BTStates.ConnectedToDevice
//        NSNotificationCenter.defaultCenter().postNotificationName(BLEServiceStartNotification, object: self, userInfo: nil)
    }
    
    func startDiscoveringServices() {
        //        self.peripheral?.discoverServices([BLEServiceUUID])
    }

    /// Check characteristic from service, discover characteristic from common UUID
    func peripheral(peripheral: CBPeripheral!, didDiscoverServices error: NSError!) {
        if((error) != nil) {
            printLog(error, "didDiscoverServices", "Error when discovering services")
            return
        }
        
        for service in peripheral.services as! [CBService] {
            if service.characteristics != nil {
                printLog(service.characteristics, "didDiscoverServices", "Characteristics already known")
            }
            if service.UUID.isEqual(uartServiceUUID()) {
                peripheral.discoverCharacteristics([txCharacteristicUUID(), rxCharacteristicUUID()], forService: service)
            }
        }
    }
    
    /// Notify peripheral that characteristic is discovered
    func peripheral(peripheral: CBPeripheral!, didDiscoverCharacteristicsForService service: CBService!, error: NSError!) {
        
        printLog(service.characteristics, "didDiscoverCharacteristicsForService", "Discover characteristique")
        
        if service.UUID.isEqual(uartServiceUUID()) {
            for characteristic in service.characteristics as! [CBCharacteristic] {
                if characteristic.UUID.isEqual(txCharacteristicUUID()) || characteristic.UUID.isEqual(rxCharacteristicUUID()) {
                    
                    peripheral.setNotifyValue(true, forCharacteristic: characteristic)
                }
            }
        }
    }
    
    /// Check update for characteristic and call didReceiveDatasFromBle method
    func peripheral(peripheral: CBPeripheral!, didUpdateValueForCharacteristic characteristic: CBCharacteristic!, error: NSError!) {

        if let dataBlock = bluetoothBuffer.addValue(characteristic.value) {
            self.blockDataCompleted = dataBlock
        }

        if !self.isReceivingDatas {
            self.isReceivingDatas = true
        }
    }
    
    /// Shorthand method that convert string into data and call writeRawData
    func writeString(string:NSString){
        
        printLog(string, "writeString", "Write data to sensmove sole")
        
        let data = NSData(bytes: string.UTF8String, length: string.length)
        
        if self.currentPeripheral != nil {
            self.writeRawData(data)
        } else {
            printLog(self.currentPeripheral, "writeString", "No current peripheral set")
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
            self.currentPeripheral.writeValue(data, forCharacteristic: txCharacteristic, type: writeType)
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
                self.currentPeripheral.writeValue(newData, forCharacteristic: self.txCharacteristic, type: writeType)
                
                loc += len
                idx += 1
            }
        }
    }
    
}