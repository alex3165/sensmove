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

protocol BLEPeripheralDelegate: Any {
    
    var connectionMode:ConnectionMode { get }
    func didReceiveData(newData:NSData)
    func connectionFinalized()
    func uartDidEncounterError(error:NSString)
    
}

class BLEPeripheral: NSObject, CBPeripheralDelegate {

    var currentPeripheral:CBPeripheral!
    var delegate:BLEPeripheralDelegate!
    var uartService:CBService?
    var rxCharacteristic:CBCharacteristic?
    var txCharacteristic:CBCharacteristic?
    var knownServices:[CBService] = []
    
    init(peripheral:CBPeripheral, delegate:BLEPeripheralDelegate){
        super.init()

        self.currentPeripheral = peripheral
        self.currentPeripheral.delegate = self
        self.delegate = delegate
    }
    
    func didConnect(withMode:ConnectionMode) {
        //Already discovered services
        if currentPeripheral.services != nil{
            printLog(self, "didConnect", "Skipping service discovery")
            peripheral(currentPeripheral, didDiscoverServices: nil)  //already discovered services, DO NOT re-discover. Just pass along the peripheral.
            return
        }
        
        printLog(self, "didConnect", "Starting service discovery")
        
        switch withMode.rawValue {
        case ConnectionMode.UART.rawValue,
        ConnectionMode.PinIO.rawValue,
        ConnectionMode.Controller.rawValue:
            currentPeripheral.discoverServices([uartServiceUUID()])
        case ConnectionMode.Info.rawValue:
            currentPeripheral.discoverServices(nil)
            break
        default:
            //printLog(self, "didConnect", "non-matching mode")
            break
        }
    }

    func peripheral(peripheral: CBPeripheral!, didDiscoverServices error: NSError!) {
        
        //Respond to finding a new service on peripheral
        
        if error != nil {
            
            //            handleError("\(self.classForCoder.description()) didDiscoverServices : Error discovering services")
            printLog(self, "didDiscoverServices", "\(error.debugDescription)")
            
            return
        }
        
        //        println("\(self.classForCoder.description()) didDiscoverServices")
        
        
        let services = peripheral.services as! [CBService]
        
        for s in services {
            
            // Service characteristics already discovered
            if (s.characteristics != nil){
                self.peripheral(peripheral, didDiscoverCharacteristicsForService: s, error: nil)    // If characteristics have already been discovered, do not check again
            }
                
                //UART or Pin I/O mode
            else if delegate.connectionMode == ConnectionMode.UART || delegate.connectionMode == ConnectionMode.PinIO || delegate.connectionMode == ConnectionMode.Controller {
                if UUIDsAreEqual(s.UUID, uartServiceUUID()) {
                    uartService = s
                    peripheral.discoverCharacteristics([txCharacteristicUUID(), rxCharacteristicUUID()], forService: uartService)
                }
            }
                
                // Info mode
            else if delegate.connectionMode == ConnectionMode.Info {
                knownServices.append(s)
                peripheral.discoverCharacteristics(nil, forService: s)
            }
            
            // Device Information
            //            else if UUIDsAreEqual(s.UUID, BLEPeripheral.deviceInformationServiceUUID()){
            //                println("\(self.classForCoder.description()) didDiscoverServices : Device Information")
            //                peripheral.discoverCharacteristics(nil, forService: s)
            //            }
        }
        
        printLog(self, "didDiscoverServices", "all top-level services discovered")
        
    }

    
    
    func peripheral(peripheral: CBPeripheral!, didDiscoverCharacteristicsForService service: CBService!, error: NSError!) {
        
        //Respond to finding a new characteristic on service
        
        if error != nil {
            //            handleError("Error discovering characteristics")
            printLog(self, "didDiscoverCharacteristicsForService", "\(error.debugDescription)")
            
            return
        }
        
        printLog(self, "didDiscoverCharacteristicsForService", "\(service.description) with \(service.characteristics.count) characteristics")
        
        // UART mode
        if delegate.connectionMode == ConnectionMode.UART || delegate.connectionMode == ConnectionMode.PinIO || delegate.connectionMode == ConnectionMode.Controller {
            
            for c in (service.characteristics as! [CBCharacteristic]) {
                
                switch c.UUID {
                case rxCharacteristicUUID():         //"6e400003-b5a3-f393-e0a9-e50e24dcca9e"
                    printLog(self, "didDiscoverCharacteristicsForService", "\(service.description) : RX")
                    rxCharacteristic = c
                    currentPeripheral.setNotifyValue(true, forCharacteristic: rxCharacteristic)
                    break
                case txCharacteristicUUID():         //"6e400002-b5a3-f393-e0a9-e50e24dcca9e"
                    printLog(self, "didDiscoverCharacteristicsForService", "\(service.description) : TX")
                    txCharacteristic = c
                    break
                default:
                    //                    printLog(self, "didDiscoverCharacteristicsForService", "Found Characteristic: Unknown")
                    break
                }
                
            }
            
            if rxCharacteristic != nil && txCharacteristic != nil {
                dispatch_async(dispatch_get_main_queue(), { () -> Void in
                    self.delegate.connectionFinalized()
                })
            }
        }
            
            // Info mode
        else if delegate.connectionMode == ConnectionMode.Info {
            
            for c in (service.characteristics as! [CBCharacteristic]) {
                
                //Read readable characteristic values
                if (c.properties.rawValue & CBCharacteristicProperties.Read.rawValue) != 0 {
                    peripheral.readValueForCharacteristic(c)
                }
                
                peripheral.discoverDescriptorsForCharacteristic(c)
                
            }
            
        }
        
    }
    
    
    func peripheral(peripheral: CBPeripheral!, didDiscoverDescriptorsForCharacteristic characteristic: CBCharacteristic!, error: NSError!) {
        
        if error != nil {
            //            handleError("Error discovering descriptors \(error.debugDescription)")
            printLog(self, "didDiscoverDescriptorsForCharacteristic", "\(error.debugDescription)")
            //            return
        }
            
        else {
            if characteristic.descriptors.count != 0 {
                for d in characteristic.descriptors {
                    let desc = d as! CBDescriptor
                    printLog(self, "didDiscoverDescriptorsForCharacteristic", "\(desc.description)")
                    
                    //                    currentPeripheral.readValueForDescriptor(desc)
                }
            }
            
        }
        
        
        //Check if all characteristics were discovered
        var allCharacteristics:[CBCharacteristic] = []
        for s in knownServices {
            for c in s.characteristics {
                allCharacteristics.append(c as! CBCharacteristic)
            }
        }
        for idx in 0...(allCharacteristics.count-1) {
            if allCharacteristics[idx] === characteristic {
                //                println("found characteristic index \(idx)")
                if (idx + 1) == allCharacteristics.count {
                    //                    println("found last characteristic")
                    if delegate.connectionMode == ConnectionMode.Info {
                        delegate.connectionFinalized()
                    }
                }
            }
        }
        
        
    }
    
    
    //    func peripheral(peripheral: CBPeripheral!, didUpdateValueForDescriptor descriptor: CBDescriptor!, error: NSError!) {
    //
    //        if error != nil {
    ////            handleError("Error reading descriptor value \(error.debugDescription)")
    //            printLog(self, "didUpdateValueForDescriptor", "\(error.debugDescription)")
    ////            return
    //        }
    //
    //        else {
    //            println("descriptor value = \(descriptor.value)")
    //            println("descriptor description = \(descriptor.description)")
    //        }
    //
    //    }
    
    
    func peripheral(peripheral: CBPeripheral!, didUpdateValueForCharacteristic characteristic: CBCharacteristic!, error: NSError!) {
        
        //Respond to value change on peripheral
        
        if error != nil {
            //            handleError("Error updating value for characteristic\(characteristic.description.utf8) \(error.description.utf8)")
            printLog(self, "didUpdateValueForCharacteristic", "\(error.debugDescription)")
            return
        }
        
        //UART mode
        if delegate.connectionMode == ConnectionMode.UART || delegate.connectionMode == ConnectionMode.PinIO || delegate.connectionMode == ConnectionMode.Controller {
            
            if (characteristic == self.rxCharacteristic){
                
                dispatch_async(dispatch_get_main_queue(), { () -> Void in
                    self.delegate.didReceiveData(characteristic.value)
                })
                
            }
                //TODO: Finalize for info mode
            else if UUIDsAreEqual(characteristic.UUID, softwareRevisionStringUUID()) {
                
                var swRevision = NSString(string: "")
                let bytes:UnsafePointer<Void> = characteristic.value.bytes
                //    const uint8_t *bytes = characteristic.value.bytes;
                
                for i in 0...characteristic.value.length {  //TODO: Check
                    
                    swRevision = NSString(format: "0x%x", UInt8(bytes[i]) ) //TODO: Check
                }
                
                dispatch_async(dispatch_get_main_queue(), { () -> Void in
                    self.delegate.connectionFinalized()
                })
            }
            
        }
        
        
    }
    
    
    func peripheral(peripheral: CBPeripheral!, didDiscoverIncludedServicesForService service: CBService!, error: NSError!) {
        
        //Respond to finding a new characteristic on service
        
        if error != nil {
            printLog(self, "didDiscoverIncludedServicesForService", "\(error.debugDescription)")
            return
        }
        
        printLog(self, "didDiscoverIncludedServicesForService", "service: \(service.description) has \(service.includedServices.count) included services")
        
        //        if service.characteristics.count == 0 {
        //            currentPeripheral.discoverIncludedServices(nil, forService: service)
        //        }
        
        for s in (service.includedServices as! [CBService]) {
            
            printLog(self, "didDiscoverIncludedServicesForService", "\(s.description)")
        }
        
    }
    
    
    func handleError(errorString:String) {
        
        printLog(self, "Error", "\(errorString)")
        
        dispatch_async(dispatch_get_main_queue(), { () -> Void in
            self.delegate.uartDidEncounterError(errorString)
        })
        
    }

}