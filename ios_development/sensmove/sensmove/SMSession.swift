//
//  SMSession.swift
//  sensmove
//
//  Created by RIEUX Alexandre on 13/04/2015.
//  Copyright (c) 2015 ___alexprod___. All rights reserved.
//

import Foundation

// Keys constant for session
let kSessionId: String = "id"
let kSessionName: String = "name"
let kSessionDate: String = "date"
let kSessionDuration: String = "duration"
let kAverageLeftForce: String = "leftForce"
let kAverageRightForce: String = "rightForce"
let kRightSole: String = "rightSole"
let kLeftSole: String = "leftSole"
let kSessionComment: String = "sessionComment"
let kSessionActivity: String = "activity"

class SMSession: NSObject {
    
    let id: String
    var name: NSString
    let date: NSDate
    var duration: NSTimeInterval?
    var averageLeftForce: NSNumber?
    var averageRightForce: NSNumber?
    var sessionComment: String?
    var activity: String?

    /// Observable variable
    dynamic var isActive: Bool
    
    var rightSole: SMSole?
    var leftSole: SMSole?

    /**
    *
    *   Initialize new active session
    *   - parameter  sensorsVectors:  x / y positions of vectors
    */
    init(sensorsVectors: JSON){
        self.name = "new_session"
        self.date = NSDate()
        self.id = "\(self.name)\(self.date.timeIntervalSince1970)"
        self.isActive = true
        self.sessionComment = ""
        self.activity = ""
        self.rightSole = SMSole(simpleVectors: sensorsVectors["right"], isRight: true)
        self.leftSole = SMSole(simpleVectors: sensorsVectors["left"], isRight: false)
        
        super.init()
    }

    /**
    *
    *   Initialize session from data file (stored session typicaly), furthermore, data will be fetched from Server
    *   - parameter  sessionSettings:  session settings from data file
    */
    init(sessionSettings: JSON) {
        self.id = sessionSettings[kSessionId].stringValue
        self.name = sessionSettings[kSessionName].stringValue
        self.date = NSDate(timeIntervalSince1970: sessionSettings[kSessionDate].doubleValue)
        self.isActive = false

        super.init()
        
        self.duration = sessionSettings[kSessionDuration].doubleValue
        self.averageLeftForce = sessionSettings[kAverageLeftForce].numberValue
        self.averageRightForce = sessionSettings[kAverageRightForce].numberValue
        
        self.sessionComment = self.getPropertyValueFromKey(kSessionComment, settings: sessionSettings)
        self.activity = self.getPropertyValueFromKey(kSessionActivity, settings: sessionSettings)

        self.rightSole = nil
        self.leftSole = nil
    }
    
    /// Get property from settings if not nul otherwise return empty String
    func getPropertyValueFromKey(key: String, settings: JSON) -> String {
        return settings[key] != nil ? settings[key].stringValue : ""
    }

    func setDuration(timeInterval: NSTimeInterval) {
        self.duration = timeInterval
    }
    
    func addDatasBlock(datas: JSON) {
        let forces = datas["fsr"].arrayValue

        self._addForcesValues(forces)
        // TODO: Add accelerometer values
    }

    func _addForcesValues(valuesArray: [JSON]) {
        self.rightSole?.updateEveryForceSensors(valuesArray)
    }
    
    func stopSession(duration: NSTimeInterval) {
        self.averageRightForce = self.rightSole?.getTotalAverageForce()
        self.averageLeftForce = self.leftSole?.getTotalAverageForce()
        
        self.setDuration(duration)
    }
    
    /**
    *
    *   Format session object for keychain storage
    *   - returns:  sessionJson  session formatted for storage
    */
    func toPropertyList() -> NSDictionary {

        let sessionJson: NSMutableDictionary = [
            kSessionId: self.id,
            kSessionName: self.name,
            kSessionDate: self.date.timeIntervalSince1970,
            kSessionDuration: self.duration!,
            kAverageLeftForce: self.averageLeftForce!,
            kAverageRightForce: self.averageRightForce!,
            kSessionActivity: self.activity!,
            kSessionComment: self.sessionComment!
        ]

        if self.rightSole != nil {
            sessionJson.setValue(self.rightSole!.toPropertyList(), forKey: kRightSole)
        }

        if self.leftSole != nil {
            sessionJson.setValue(self.leftSole!.toPropertyList(), forKey: kLeftSole)
        }

        return sessionJson
    }
}