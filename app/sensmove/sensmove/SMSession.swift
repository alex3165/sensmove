//
//  SMSession.swift
//  sensmove
//
//  Created by RIEUX Alexandre on 13/04/2015.
//  Copyright (c) 2015 ___alexprod___. All rights reserved.
//

import Foundation

class SMSession: NSObject {
    
    var id: NSString
    var name: NSString
    var date: NSDate
    var duration: NSNumber
    var averageLeftForce: NSNumber?
    var averageRightForce: NSNumber?

    init(sessionSettings: JSON) {

        self.id = sessionSettings["id"].stringValue
        self.name = sessionSettings["name"].stringValue
        self.date = NSDate(timeIntervalSince1970: sessionSettings["date"].doubleValue)
        self.duration = sessionSettings["duration"].numberValue
        self.averageLeftForce = sessionSettings["leftForce"].numberValue
        self.averageRightForce = sessionSettings["rightForce"].numberValue

        super.init()
    }

    func toPropertyList() -> NSDictionary {
        var sessionJson: NSDictionary = [
            "id": self.id,
            "name": self.name,
            "date": self.date.timeIntervalSince1970,
            "duration": self.duration,
            "leftForce": self.averageLeftForce!,
            "rightForce": self.averageRightForce!
        ]
        return sessionJson
    }
}