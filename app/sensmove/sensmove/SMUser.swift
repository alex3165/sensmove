//
//  SMUser.swift
//  sensmove
//
//  Created by RIEUX Alexandre on 12/04/2015.
//  Copyright (c) 2015 ___alexprod___. All rights reserved.
//

import Foundation

class SMUser: NSObject {

    var name: NSString?
    var weight: NSNumber?
    var height: NSNumber?

    var doctor: NSString?
    var balance: NSString?
    var averageForceLeft: NSNumber?
    var averageForceRight: NSNumber?

    var diseaseDescription: NSString?
    
    init(userSettings: JSON) {
        self.name = userSettings["name"].stringValue
        self.weight = userSettings["weight"].numberValue
        self.height = userSettings["height"].numberValue
        
        self.doctor = userSettings["doctor"].stringValue
        self.balance = userSettings["balance"].stringValue
        self.averageForceLeft = userSettings["averageForceLeft"].numberValue
        self.averageForceRight = userSettings["averageForceRight"].numberValue
            
        self.diseaseDescription = userSettings["diseaseDescription"].stringValue
        
        super.init()
    }

    func saveUserToKeychain() {
        var userInformations: JSON = toPropertyList()
        var datas : NSData = userInformations.rawData()!
        NSUserDefaults.standardUserDefaults().setObject(datas, forKey: "user")
    }

    class func getUserFromKeychain() -> (SMUser?) {
        var savedData: NSData? = NSUserDefaults.standardUserDefaults().dataForKey("user")
        
        if(savedData != nil) {
            var jsonDatas: JSON = JSON(data: savedData!)
            return SMUser(userSettings: jsonDatas)
        }

        return nil
    }
    
    func removeUserFromKeychain() {
        NSUserDefaults.standardUserDefaults().removeObjectForKey("user")
        self.name = nil
        self.weight = nil
        self.height = nil
        self.doctor = nil
        self.balance = nil
        self.averageForceLeft = nil
        self.averageForceRight = nil
        self.diseaseDescription = nil
    }

    private func toPropertyList() -> JSON {
        var userJson: JSON = [
            "name": self.name!,
            "weight": self.weight!,
            "height": self.height!,
            "doctor": self.doctor!,
            "balance": self.balance!,
            "averageForceLeft": self.averageForceLeft!,
            "averageForceRight": self.averageForceRight!
        ]

        return userJson
    }
    
}