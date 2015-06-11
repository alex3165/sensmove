//
//  SMBLEPeripheral.swift
//  sensmove
//
//  Created by RIEUX Alexandre on 16/05/2015.
//  Copyright (c) 2015 ___alexprod___. All rights reserved.
//

import Foundation
import CoreBluetooth

enum ConnectionMode:Int {
    case PinIO
    case UART
    case DeviceList
    case Info
    case Controller
}

protocol SMBLEPeripheralDelegate: Any {

    var connectionMode:ConnectionMode { get }
    func didReceiveData(newData:NSData)
    func connectionFinalized()
    func uartDidEncounterError(error:NSString)

}

class SMBLEPeripheral: NSObject, CBPeripheralDelegate {

    var currentPeripheral:CBPeripheral!
    var delegate:SMBLEPeripheralDelegate!
    var uartService:CBService?
    var rxCharacteristic:CBCharacteristic?
    var txCharacteristic:CBCharacteristic?
    var knownServices:[CBService] = []

    init(peripheral:CBPeripheral, delegate:SMBLEPeripheralDelegate){
        super.init()
        self.currentPeripheral = peripheral
        self.currentPeripheral.delegate = self
        self.delegate = delegate
    }
    
    func didConnect(withMode:ConnectionMode) {

    }

    func peripheral(peripheral: CBPeripheral!, didDiscoverServices error: NSError!) {

    }
    
    func peripheral(peripheral: CBPeripheral!, didDiscoverCharacteristicsForService service: CBService!, error: NSError!) {

    }
    
    func peripheral(peripheral: CBPeripheral!, didDiscoverDescriptorsForCharacteristic characteristic: CBCharacteristic!, error: NSError!) {
        
    }
    
    
    //    func peripheral(peripheral: CBPeripheral!, didUpdateValueForDescriptor descriptor: CBDescriptor!, error: NSError!) {
    //
    //        if error != nil {
    //            handleError("Error reading descriptor value \(error.debugDescription)")
    //            printLog(self, "didUpdateValueForDescriptor", "\(error.debugDescription)")
    //            return
    //        }
    //
    //        else {
    //            println("descriptor value = \(descriptor.value)")
    //            println("descriptor description = \(descriptor.description)")
    //        }
    //
    //    }
    
    
    func peripheral(peripheral: CBPeripheral!, didUpdateValueForCharacteristic characteristic: CBCharacteristic!, error: NSError!) {

    }
    
    
    func peripheral(peripheral: CBPeripheral!, didDiscoverIncludedServicesForService service: CBService!, error: NSError!) {
        
    }


}