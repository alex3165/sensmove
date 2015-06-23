//
//  SMTrackController.swift
//  sensmove
//
//  Created by RIEUX Alexandre on 24/05/2015.
//  Copyright (c) 2015 ___alexprod___. All rights reserved.
//

import UIKit
import CoreBluetooth
import Foundation
import SceneKit

class SMTrackController: UIViewController, CBCentralManagerDelegate, CBPeripheralDelegate, SMChronometerDelegate {
    
    // For development
    //@IBOutlet weak var printButton: UIButton?
    
    @IBOutlet weak var timeCountdown: UILabel?
    @IBOutlet weak var stopSessionButton: UIButton?

    var chronometer: SMChronometer?
    var trackSessionService: SMTrackSessionService?
    
    // Current central manager
    var centralManager: CBCentralManager?
    
    // Current received datas
    var datas: NSMutableData?
    
    // current discovered peripheral
    private var currentPeripheral: CBPeripheral?
    
    
    override func viewDidLoad() {
        super.viewDidLoad()
        
        self.trackSessionService = SMTrackSessionService.sharedInstance
        
        /// Trigger new session when opening track controller
        self.trackSessionService?.createNewSession()
        self.chronometer = SMChronometer()
        self.chronometer?.delegate = self
        self.chronometer?.startChronometer()

        self.datas = NSMutableData()

        
        self.centralManager = CBCentralManager(delegate: self, queue: nil)

        //self.peripheral = SMBLEPeripheral()
//        RACObserve(self, "datas").subscribeNext { (datas) -> Void in
//        }

        /// For development
        var bleSimulator = SMBluetoothSimulator()
        RACObserve(bleSimulator, "data").subscribeNext { (next:AnyObject!) -> Void in
            
            if let data = next as? NSData {
                self.didReceiveDatasFromBle(data)
            }
        }
        /// ******************
        
        self.uiInitialize()
    }

    override func didReceiveMemoryWarning() {
        super.didReceiveMemoryWarning()
    }
    
    func uiInitialize() {
        self.stopSessionButton?.backgroundColor = SMColor.red()
        self.stopSessionButton?.setTitleColor(SMColor.whiteColor(), forState: UIControlState.Normal)
    }

    /**
    *
    *   Delegate method triggered every second
    *   :param: newTime new time string formated
    *
    */
    func updateChronometer(newTime: String) {
        self.timeCountdown?.text = newTime
    }
    
    @IBAction func stopSessionAction(sender: AnyObject) {
        
    }

     /// MARK: Central manager delegates methods
    func centralManagerDidUpdateState(central: CBCentralManager!){
        if(centralManager?.state == CBCentralManagerState.PoweredOn) {

            self.centralManager?.scanForPeripheralsWithServices(nil, options: [CBCentralManagerScanOptionAllowDuplicatesKey: NSNumber(bool: true)])
            printLog(self, "centralManagerDidUpdateState", "Scanning")
        }
    }
    
    func centralManager(central: CBCentralManager!, didDiscoverPeripheral peripheral: CBPeripheral!, advertisementData: [NSObject : AnyObject]!, RSSI: NSNumber!) {
        if(self.currentPeripheral != peripheral && peripheral.name == "SL18902"){
            self.currentPeripheral = peripheral
            
            //SMBLEPeripheral(peripheral: self.currentPeripheral!)

            self.centralManager?.connectPeripheral(peripheral, options: nil)
        }
    }
    
    func centralManager(central: CBCentralManager!, didConnectPeripheral peripheral: CBPeripheral!) {
        
        self.centralManager?.stopScan()
        
        self.datas?.length = 0
        
        peripheral.delegate = self
        
        if peripheral.services == nil {
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

        printLog(service.characteristics, "didDiscoverCharacteristicsForService", "Discover characteristique")
        
        if service.UUID.isEqual(uartServiceUUID()) {
            for characteristic in service.characteristics as! [CBCharacteristic] {
                if characteristic.UUID.isEqual(txCharacteristicUUID()) || characteristic.UUID.isEqual(rxCharacteristicUUID()) {
                    peripheral.setNotifyValue(true, forCharacteristic: characteristic)
                }
            }
        }
    }
    
    func peripheral(peripheral: CBPeripheral!, didUpdateValueForCharacteristic characteristic: CBCharacteristic!, error: NSError!) {
        printLog(characteristic, "didUpdateValueForCharacteristic", "Append new datas")
        self.didReceiveDatasFromBle(characteristic.value)
    }

    func didReceiveDatasFromBle(datas: NSData) {
        var jsonData: JSON = JSON(data: datas)
        var fsr: Array<JSON> = jsonData["fsr"].arrayValue
        
    }
    
    // MARK: tests methods
//    @IBAction func startAction(sender:UIButton!) {
//
//    }
//    
//    @IBAction func printAction(sender:UIButton!) {
//    }

}
