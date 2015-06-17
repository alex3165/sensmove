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

class SMTrackController: UIViewController, CBCentralManagerDelegate, CBPeripheralDelegate {
    
    enum ConnectionStatus:Int {
        case Idle = 0
        case Scanning
        case Connected
        case Connecting
    }
    
    @IBOutlet weak var printButton: UIButton?
    @IBOutlet weak var solesGraph: SCNView?

    var trackSessionService: SMTrackSessionService?
    
    var peripheral: SMBLEPeripheral?
    
    // Current central manager
    var centralManager: CBCentralManager?
    
    // Current received datas
    var datas:NSMutableData?
    
    // current discovered peripheral
    private var currentPeripheral:CBPeripheral?
    
    var smLiveGraph: SMLiveForcesTrack?
    
    override func viewDidLoad() {
        super.viewDidLoad()

//        self.solesGraph?.autoenablesDefaultLighting = true
        self.solesGraph?.allowsCameraControl = true

        self.trackSessionService = SMTrackSessionService.sharedInstance
        self.trackSessionService?.createNewSession()

        self.datas = NSMutableData()
        
        self.smLiveGraph = SMLiveForcesTrack()
        self.solesGraph?.scene = self.smLiveGraph

        
        self.centralManager = CBCentralManager(delegate: self, queue: nil)

        //self.peripheral = SMBLEPeripheral()
//        RACObserve(self, "datas").subscribeNext { (datas) -> Void in
//            
//        }
        var bleSimulator = SMBluetoothSimulator()
        
        RACObserve(bleSimulator, "data").subscribeNext { (next:AnyObject!) -> Void in
            
            if let data = next as? NSData {
                self.didReceiveDatasFromBle(data)
            }
        }
        
        self.initCharts()
    }
    
    func initCharts() {
        var barChart: PNBarChart = PNBarChart(frame: CGRectMake(0, 135.0, UIScreen.mainScreen().bounds.width, 200.0))
//        [barChart setXLabels:@[@"SEP 1",@"SEP 2",@"SEP 3",@"SEP 4",@"SEP 5"]];
//        [barChart setYValues:@[@1,  @10, @2, @6, @3]];
//        [barChart strokeChart];
    }

    override func didReceiveMemoryWarning() {
        super.didReceiveMemoryWarning()
        // Dispose of any resources that can be recreated.
    }

    // MARK: Central manager delegates methods
    func centralManagerDidUpdateState(central: CBCentralManager!){
        if(centralManager?.state == CBCentralManagerState.PoweredOn) {
            self.centralManager?.scanForPeripheralsWithServices(nil, options: [CBCentralManagerScanOptionAllowDuplicatesKey: NSNumber(bool: true)])
            printLog(self, "centralManagerDidUpdateState", "Scanning")
        }
    }
    
    
    func centralManager(central: CBCentralManager!, didDiscoverPeripheral peripheral: CBPeripheral!, advertisementData: [NSObject : AnyObject]!, RSSI: NSNumber!) {
        if(self.currentPeripheral != peripheral && peripheral.name == "SENS"){
            self.currentPeripheral = peripheral
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

    func didReceiveDatasFromBle(datas: NSData){
        var jsonData: JSON = JSON(data: datas)
        var fsr: Array<JSON> = jsonData["fsr"].arrayValue
        
    }
    
    // MARK: tests methods
    @IBAction func startAction(sender:UIButton!) {
//        self.solesGraph?
    }
    
    @IBAction func printAction(sender:UIButton!) {

//        for node: SCNNode in self.smLiveGraph?.rootNode.childNodes as! [SCNNode] {
//            print("x: \(node.position.x), y: \(node.position.y) |||")
//        }
        
    }

}
