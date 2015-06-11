//
//  SMBLECentral.swift
//  sensmove
//
//  Created by RIEUX Alexandre on 16/05/2015.
//  Copyright (c) 2015 ___alexprod___. All rights reserved.
//

import Foundation
import CoreBluetooth

class SMBLECentral: NSObject, CBCentralManagerDelegate, CBPeripheralDelegate {

    // All connection status possibles
    enum ConnectionStatus:Int {
        case Idle = 0
        case Scanning
        case Connected
        case Connecting
    }

    // Current central manager
    var centralManager:CBCentralManager?
    
    // Current received datas
    var datas:NSMutableData?
    
    // Current connection status
    var connectionStatus:ConnectionStatus = ConnectionStatus.Idle

    // current discovered peripheral
    private var currentPeripheral:CBPeripheral?

    override init(){
        super.init()
        self.centralManager = CBCentralManager(delegate: self, queue: nil)

        self.startScan()
    }

    func startScan(){
        self.centralManager?.scanForPeripheralsWithServices(nil, options: [CBCentralManagerScanOptionAllowDuplicatesKey: NSNumber(bool: true)])
    }

    func centralManagerDidUpdateState(central: CBCentralManager!){
        if(centralManager?.state == CBCentralManagerState.PoweredOn) {
            self.centralManager?.scanForPeripheralsWithServices([uartServiceUUID()], options: [CBCentralManagerScanOptionAllowDuplicatesKey: NSNumber(bool: true)])
            printLog(self, "centralManagerDidUpdateState", "Scanning")
        }
    }
    

    func centralManager(central: CBCentralManager!, didDiscoverPeripheral peripheral: CBPeripheral!, advertisementData: [NSObject : AnyObject]!, RSSI: NSNumber!) {

        if(self.currentPeripheral != peripheral){
            self.currentPeripheral = peripheral
            self.centralManager?.connectPeripheral(peripheral, options: nil)
        }
    }

    func centralManager(central: CBCentralManager!, didConnectPeripheral peripheral: CBPeripheral!) {
    
        self.centralManager?.stopScan()
        
        self.datas?.length = 0
        
        peripheral.delegate = self

        if peripheral.services != nil {
            peripheral.discoverServices([uartServiceUUID()])
        }
    }

    func peripheral(peripheral: CBPeripheral!, didDiscoverServices error: NSError!) {
        
        if((error) != nil) {
            printLog(error, "didDiscoverServices", "error when discovering services")
            return
        }
        
        for service in peripheral.services as! [CBService] {
            if service.characteristics != nil {
                printLog(service.characteristics, "didDiscoverServices", "characteristics already known")
            }
            if service.UUID.isEqual(uartServiceUUID()) {
                peripheral.discoverCharacteristics([txCharacteristicUUID(), rxCharacteristicUUID()], forService: service)
            }
        }
    }
    
    func peripheral(peripheral: CBPeripheral!, didDiscoverCharacteristicsForService service: CBService!, error: NSError!) {
    
        if service.UUID.isEqual(uartServiceUUID()) {
            for characteristic in service.characteristics as! [CBCharacteristic] {
                if characteristic.UUID.isEqual(txCharacteristicUUID()) || characteristic.UUID.isEqual(rxCharacteristicUUID()) {
                    peripheral.setNotifyValue(true, forCharacteristic: characteristic)
                }
            }
        }
        
    }
    
    func peripheral(peripheral: CBPeripheral!, didUpdateValueForCharacteristic characteristic: CBCharacteristic!, error: NSError!) {
        self.datas?.appendData(characteristic.value)
    }
}