//
//  SMBLEDiscovery.swift
//  sensmove
//
//  Created by alexandre on 24/08/2015.
//  Copyright (c) 2015 ___alexprod___. All rights reserved.
//

import Foundation
import CoreBluetooth

let btDiscoverySharedInstance = SMBLEDiscovery()

class SMBLEDiscovery: NSObject, CBCentralManagerDelegate {

    var centralManager: CBCentralManager?
    var peripheralBLE: CBPeripheral?
    
    override init() {
        super.init()
        let centralQueue = dispatch_queue_create("fr.sensmove", DISPATCH_QUEUE_SERIAL)

        self.centralManager = CBCentralManager(delegate: self, queue: centralQueue)
    }
    
    dynamic var bleService: SMBLEService? {
        didSet {
            if let service = self.bleService {
                service.startDiscoveringServices()
            }
        }
    }
    /// Triggered whenever bluetooth state change, verify if it's power is on then scan for peripheral
    func centralManagerDidUpdateState(central: CBCentralManager!){
        if(self.centralManager?.state == CBCentralManagerState.PoweredOn) {
            self.centralManager?.scanForPeripheralsWithServices(nil, options: [CBCentralManagerScanOptionAllowDuplicatesKey: NSNumber(bool: true)])
            printLog(self, "centralManagerDidUpdateState", "Scanning")
        }
    }
    
    /// Connect to peripheral from name
    func centralManager(central: CBCentralManager!, didDiscoverPeripheral peripheral: CBPeripheral!, advertisementData: [NSObject : AnyObject]!, RSSI: NSNumber!) {
        if (peripheral.name != nil) {
            if(self.peripheralBLE != peripheral && peripheral.name == "coco"){
                self.peripheralBLE = peripheral
                
                self.centralManager?.connectPeripheral(peripheral, options: nil)
            }
        }
    }
    
    /// Triggered when device is connected to peripheral, check for services
    func centralManager(central: CBCentralManager!, didConnectPeripheral peripheral: CBPeripheral!) {
        
        if (peripheral == nil) {
            return;
        }
        
        if (peripheral == self.peripheralBLE) {
            self.bleService = SMBLEService(initWithPeripheral: peripheral)
        }
        
        if peripheral.services == nil {
            peripheral.discoverServices([uartServiceUUID()])
        }
        
        self.centralManager?.stopScan()
    }
    
    func centralManager(central: CBCentralManager!, didDisconnectPeripheral peripheral: CBPeripheral!, error: NSError!) {
        if (peripheral == nil) {
            return;
        }

        println("Disconnected from insole \(peripheral.name)")
        
        self.bleService?.isConnectedToDevice = false
        self.bleService?.isReceivingDatas = false
    }
}
