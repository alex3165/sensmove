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

class SMSession: NSObject {
    
    var id: NSString
    var name: NSString
    var date: NSDate
    var duration: NSNumber?
    var averageLeftForce: NSNumber?
    var averageRightForce: NSNumber?

    /// Observable variable
    dynamic var isActive: Bool
    
    var rightSole: SMSole?
    var leftSole: SMSole?

    /**
    *
    *   Initialize new active session
    *   :param:  sensorsVectors  x / y positions of vectors
    */
    init(sensorsVectors: JSON){
        self.name = "new_session"
        self.date = NSDate()
        self.id = NSString(format:"%@%ui", self.name, self.date.timeIntervalSince1970)
        self.isActive = true

        self.rightSole = SMSole(simpleVectors: sensorsVectors["right"], isRight: true)
        self.leftSole = SMSole(simpleVectors: sensorsVectors["left"], isRight: false)
        
        super.init()
    }

    /**
    *
    *   Initialize session from data file (stored session typicaly), furthermore, data will be fetched from Server
    *   :param:  sessionSettings  session settings from data file
    */
    init(sessionSettings: JSON) {
        self.id = sessionSettings[kSessionId].stringValue
        self.name = sessionSettings[kSessionName].stringValue
        self.date = NSDate(timeIntervalSince1970: sessionSettings[kSessionDate].doubleValue)
        self.duration = sessionSettings[kSessionDuration].numberValue
        self.averageLeftForce = sessionSettings[kAverageLeftForce].numberValue
        self.averageRightForce = sessionSettings[kAverageRightForce].numberValue
        self.isActive = false
        self.rightSole = nil
        self.leftSole = nil

        super.init()
    }

    /**
    *
    *   Format session object for keychain storage
    *   :returns:  sessionJson  session formatted for storage
    */
    func toPropertyList() -> NSDictionary {
        var sessionJson: NSDictionary = [
            kSessionId: self.id,
            kSessionName: self.name,
            kSessionDate: self.date.timeIntervalSince1970,
            kSessionDuration: self.duration!,
            kAverageLeftForce: self.averageLeftForce!,
            kAverageRightForce: self.averageRightForce!
        ]
        return sessionJson
    }
}