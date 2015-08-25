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

class SMTrackController: UIViewController, SMChronometerDelegate {
    
    @IBOutlet weak var timeCountdown: UILabel?
    @IBOutlet weak var stopSessionButton: UIButton?
    @IBOutlet weak var liveTrackGraph: SMLiveForcesTrack!
    @IBOutlet weak var jumpCounter: UILabel!
    @IBOutlet weak var stepCounter: UILabel!
    
    var chronometer: SMChronometer?
    var trackSessionService: SMTrackSessionService?
    

    override func viewDidLoad() {
        super.viewDidLoad()

        self.trackSessionService = SMTrackSessionService.sharedInstance
        
        /// Trigger new session when opening track controller
        self.trackSessionService?.createNewSession()
        self.chronometer = SMChronometer()
        self.chronometer?.delegate = self
        self.chronometer?.startChronometer()
        
        let btDiscovery: SMBLEDiscovery = btDiscoverySharedInstance

        // Wait until bleService is set
        RACObserve(btDiscovery, "bleService").subscribeNext { (bleService) -> Void in
            if let btService = bleService as? SMBLEService {
                /**
                *   Observe blockDataCompleted property of current class then update forces values
                *   of current session
                */
                RACObserve(btService, "blockDataCompleted").subscribeNext { (datas) -> Void in
                    if let data: NSData = datas as? NSData {
                        let jsonObject: JSON = JSON(data: data)
                        self.trackSessionService?.updateCurrentSession(jsonObject)
                    }
                }
            }
        }

        // Initialize graph charts for each sensors
        self.liveTrackGraph.initializeCharts()

        // Initialize RACObserve on each sensors
        self.liveTrackGraph.initializeForceObserver()

        self.uiInitialize()
    }

    override func didReceiveMemoryWarning() {
        super.didReceiveMemoryWarning()
    }
    
    
    func uiInitialize() {
        
        self.jumpCounter.textColor = SMColor.orange()
        self.stepCounter.textColor = SMColor.orange()
        
        self.stopSessionButton?.backgroundColor = SMColor.red()
        self.stopSessionButton?.setTitleColor(SMColor.whiteColor(), forState: UIControlState.Normal)
    }

    /**
    *
    *   Delegate method triggered every second
    *   :param: String new time string formated
    *
    */
    func updateChronometer(newTime: String) {
        self.timeCountdown?.text = newTime
    }
    
    @IBAction func stopSessionAction(sender: AnyObject) {

        self.chronometer?.stopChronometer()
        let elapsedTime = self.chronometer?.getElapsedTime()
        self.trackSessionService?.stopCurrentSession(elapsedTime!)

        let resultController: UIViewController = self.storyboard?.instantiateViewControllerWithIdentifier("resumeController") as! UIViewController
        self.navigationController?.presentViewController(resultController, animated: false, completion: nil)
    }

}
