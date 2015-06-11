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

    class var sharedInstance: SMTrackSessionService {
        struct Static {
            static let instance: SMTrackSessionService = SMTrackSessionService()
        }
        return Static.instance
    }

    func createNewSession(){
        
        var singletonDatas: SMData = SMData.sharedInstance
        singletonDatas.getDatasFromFile("SMSensorsVectors", success: { (datas) -> () in
            
            var sensorsJson: JSON = singletonDatas.initializeSensors(JSON(data: datas))
            self.currentSession = SMSession(sensorsVectors: sensorsJson)
            
        }) { (error) -> () in
            
        }
    }

    func getRightSole() -> SMSole {
        return (self.currentSession?.rightSole as SMSole?)!
    }

    func getLeftSole() -> SMSole {
        return (self.currentSession?.leftSole as SMSole?)!
    }
    
    func sensorsValuesFromString(){
        
    }
}
