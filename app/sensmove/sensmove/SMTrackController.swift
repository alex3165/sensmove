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

class SMTrackController: UIViewController { // , SMBLEPeripheralDelegate
    
    @IBOutlet weak var solesGraph: SCNView?
    var trackSessionService: SMTrackSessionService?
    
    var smLiveGraph: SMLiveForcesTrack?
    
    override func viewDidLoad() {
        super.viewDidLoad()
        
        self.smLiveGraph = SMLiveForcesTrack()
        self.solesGraph?.scene = self.smLiveGraph

        self.solesGraph?.autoenablesDefaultLighting = true
        self.solesGraph?.allowsCameraControl = true

        self.trackSessionService = SMTrackSessionService.sharedInstance
        self.trackSessionService?.createNewSession()

//        RACObserve(self.trackSessionService?.currentSession, "rightSole").subscribeCompleted { () -> Void in
//        }
        
    }

    override func didReceiveMemoryWarning() {
        super.didReceiveMemoryWarning()
        // Dispose of any resources that can be recreated.
    }

    func didReceiveData(newData:NSData) {
        
    }

    func connectionFinalized() {
        
    }

    func uartDidEncounterError(error:NSString) {
        
    }
    /*
    // MARK: - Navigation

    // In a storyboard-based application, you will often want to do a little preparation before navigation
    override func prepareForSegue(segue: UIStoryboardSegue, sender: AnyObject?) {
        // Get the new view controller using segue.destinationViewController.
        // Pass the selected object to the new view controller.
    }
    */

}
