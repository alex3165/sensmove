//
//  SMUser.swift
//  sensmove
//
//  Created by RIEUX Alexandre on 12/04/2015.
//  Copyright (c) 2015 ___alexprod___. All rights reserved.
//

import Foundation

class SMUser: NSObject {

    var name: NSString = ""
    var weight: NSNumber?
    var height: NSNumber?
    
    var doctor: NSString = ""
    var balance: NSString = ""
    var averageForceLeft: NSNumber?
    var averageForceRight: NSNumber?
    
    var diseaseDescription: NSString = ""
    
    init(userSettings: NSDictionary) {
        super.init()

        self.name = userSettings["name"] as! NSString
        self.weight = userSettings["weight"] as? NSNumber
        self.height = userSettings["height"] as? NSNumber
            
        self.doctor = userSettings["doctor"] as! NSString
        self.balance = userSettings["balance"] as! NSString
        self.averageForceLeft = userSettings["averageForceLeft"] as? NSNumber
        self.averageForceRight = userSettings["averageForceRight"] as? NSNumber
            
        self.diseaseDescription = userSettings["diseaseDescription"] as! NSString
    }
    
    func saveUserToKeychain() {
        var userInformations = toPropertyList()
        var datas : NSData = NSKeyedArchiver.archivedDataWithRootObject(userInformations)
        NSUserDefaults.standardUserDefaults().setObject(datas, forKey: "user")
    }
    
    func getUserFromKeychain() -> SMUser {
        var userDatas: NSDictionary = NSUserDefaults.standardUserDefaults().objectForKey("user") as! NSDictionary
        init(userSettings: userDatas)

        return self;
    }
    
    private func toPropertyList() -> NSDictionary {
        var userDictionary: NSDictionary = NSDictionary.alloc()
        userDictionary["name"] = self.name
        userDictionary["weight"] = self.weight
        userDictionary["height"] = self.height
        
        
        return userDictionary
    }
    
}