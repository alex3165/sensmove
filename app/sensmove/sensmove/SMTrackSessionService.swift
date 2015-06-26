//
//  SMTrackSessionService.swift
//  sensmove
//
//  Created by RIEUX Alexandre on 31/05/2015.
//  Copyright (c) 2015 ___alexprod___. All rights reserved.
//

import Foundation
import UIKit

class SMTrackSessionService: NSObject {

    var currentSession: SMSession?

    // Singleton instance of current class
    static let sharedInstance = SMTrackSessionService()
//    class var sharedInstance: SMTrackSessionService {
//        struct Static {
//            static let instance: SMTrackSessionService = SMTrackSessionService()
//        }
//        return Static.instance
//    }

    // Create new force / acceleration tracking
    func createNewSession() {
        var singletonDatas: SMData = SMData.sharedInstance
        singletonDatas.getDatasFromFile("SMSensorsVectors", success: { (datas) -> () in
            
            var sensorsJson: JSON = singletonDatas.initializeSensors(JSON(data: datas))
            self.currentSession = SMSession(sensorsVectors: sensorsJson)
            
        }) { (error) -> () in

            printLog(error, "createNewSession", "Error when creating new session")

        }
    }
    
    func stopCurrentSession(duration: NSTimeInterval) {
        self.currentSession?.setDuration(duration)

        /// TODO: Retrieve and calcul forces from soles datas
        self.currentSession?.setLeftForce(100)
        self.currentSession?.setRightForce(110)
    }
    
    func saveCurrentSession() {
        SMUserService.sharedInstance.addSessionToCurrentUser(self.currentSession!)
        self.currentSession = nil
    }
    
    func deleteCurrentSession() {
        self.currentSession = nil
    }

    // return the right sole object
    func getRightSole() -> SMSole {
        return (self.currentSession?.rightSole as SMSole?)!
    }

    // return the left sole object
    func getLeftSole() -> SMSole {
        return (self.currentSession?.leftSole as SMSole?)!
    }
}