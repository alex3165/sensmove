//
//  SMUser.swift
//  sensmove
//
//  Created by RIEUX Alexandre on 12/04/2015.
//  Copyright (c) 2015 ___alexprod___. All rights reserved.
//

import Foundation

class SMUser: NSObject {

    var name: NSString
    var weight: NSNumber?
    var height: NSNumber?
    
    var doctor: NSString?
    var balance: NSString?
    var averageForceLeft: NSNumber?
    var averageForceRight: NSNumber?
    
    var diseaseDescription: NSString?
    
    init(userSettings: NSDictionary) {
        self.name = userSettings["name"] as! NSString
        self.weight = userSettings["weight"] as? NSNumber
        self.height = userSettings["height"] as? NSNumber
        
        self.doctor = userSettings["doctor"] as? NSString
        self.balance = userSettings["balance"] as? NSString
        self.averageForceLeft = userSettings["averageForceLeft"] as? NSNumber
        self.averageForceRight = userSettings["averageForceRight"] as? NSNumber
            
        self.diseaseDescription = userSettings["diseaseDescription"] as? NSString
    }

    func saveUserToKeychain() {
        var userInformations = toPropertyList()
        var datas : NSData = NSKeyedArchiver.archivedDataWithRootObject(userInformations)
        NSUserDefaults.standardUserDefaults().setObject(datas, forKey: "user")
    }

    class func getUserFromKeychain() -> (SMUser?) {
        var savedData = NSUserDefaults.standardUserDefaults().dataForKey("user")
        var dict = NSKeyedUnarchiver.unarchiveObjectWithData(savedData!)
        var userDatas = NSDictionary()
        
        if(dict != nil) {
            userDatas = dict as! NSDictionary
        }else{
            return nil;
        }
        
        var user: SMUser = SMUser.init(userSettings: userDatas)
        return user
    }
    
    func removeUserFromKeychain() {
        NSUserDefaults.standardUserDefaults().removeObjectForKey("user")
    }

    private func toPropertyList() -> NSDictionary {
        var userDictionary: NSDictionary = [
            "name": self.name,
            "weight": self.weight!,
            "height": self.height!,
            "doctor": self.doctor!,
            "balance": self.balance!,
            "averageForceLeft": self.averageForceLeft!,
            "averageForceRight": self.averageForceRight!
        ]

        return userDictionary
    }
    
}