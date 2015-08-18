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

    /// Singleton instance of current class
    static let sharedInstance = SMTrackSessionService()

    // Create new session from sensors parameters in SMSensorsVectors file
    func createNewSession() {
        var singletonDatas: SMData = SMData.sharedInstance
        singletonDatas.getDatasFromFile("SMSensorsVectors", success: { (datas) -> () in
            
            var sensorsJson: JSON = singletonDatas.initializeSensors(JSON(data: datas))
            self.currentSession = SMSession(sensorsVectors: sensorsJson)
            
        }) { (error) -> () in

            printLog(error, "createNewSession", "Error when creating new session")

        }
    }
    
    // Set duration and left / right forces
    func stopCurrentSession(duration: NSTimeInterval) {
        self.currentSession?.stopSession(duration)
    }

    func saveCurrentSession() {
        SMUserService.sharedInstance.addSessionToCurrentUser(self.currentSession!)
        self.deleteCurrentSession()
    }

    /**
    *   Give json datas to currentsession service, picked up from bluetooth central manager
    */
    func updateCurrentSession(jsonDatas: JSON) {
        self.currentSession?.addDatasBlock(jsonDatas)
    }

    /**
    *   Return average force according to selected sole
    */
    func getAverageForces(sole: String) -> Float {
        return sole == "left" ? self.currentSession?.averageLeftForce as! Float : self.currentSession?.averageRightForce as! Float
    }

    func deleteCurrentSession() {
        self.currentSession = nil
    }

    // return the right sole object
    func getRightSole() -> SMSole? {
        
        if let curSession = self.currentSession {
            return curSession.rightSole!
        } else {
            return nil
        }
    }

    // return left sole object
    func getLeftSole() -> SMSole {
        return (self.currentSession?.leftSole as SMSole?)!
    }
}
