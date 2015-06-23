//
//  SMUser.swift
//  sensmove
//
//  Created by RIEUX Alexandre on 12/04/2015.
//  Copyright (c) 2015 ___alexprod___. All rights reserved.
//

import Foundation

// Keys constant for user
let kId: String = "id"
let kFirstName: String = "firstname"
let kLastName: String = "lastname"
let kEmail: String = "email"
let kPicturePath: String = "picturePath"
let kWeight: String = "weight"
let kHeight: String = "height"
let kBalance: String = "balance"
let kDoctor: String = "doctor"
let kForceLeft: String = "averageForceLeft"
let kForceRight: String = "averageForceRight"
let kDiseaseDescription: String = "diseaseDescription"
let kSessions: String = "sessions"

class SMUser: NSObject {

    /// User informations
    var id: NSNumber?
    var firstName: NSString?
    var lastName: NSString?
    var email: NSString?
    var picturePath: NSString?
    var weight: NSNumber?
    var height: NSNumber?

    var doctor: NSString?
    var balance: NSString?
    var averageForceLeft: NSNumber?
    var averageForceRight: NSNumber?

    var diseaseDescription: NSString?

    dynamic var sessions: NSMutableArray?
    
    /**
    *   Init user model from user settings in params
    *   :param: userSettings the user settings
    */
    init(userSettings: JSON) {
        self.id = userSettings[kId].numberValue
        self.firstName = userSettings[kFirstName].stringValue
        self.lastName = userSettings[kLastName].stringValue
        self.email = userSettings[kEmail].stringValue
        self.picturePath = userSettings[kPicturePath].stringValue
        self.weight = userSettings[kWeight].numberValue
        self.height = userSettings[kHeight].numberValue

        self.doctor = userSettings[kDoctor].stringValue
        self.balance = userSettings[kBalance].stringValue
        self.averageForceLeft = userSettings[kForceLeft].numberValue
        self.averageForceRight = userSettings[kForceRight].numberValue
            
        self.diseaseDescription = userSettings[kDiseaseDescription].stringValue
        
        self.sessions = NSMutableArray();
        for session in userSettings[kSessions].arrayValue {
            self.sessions?.addObject(SMSession(sessionSettings: session))
        }

        super.init()
    }
    
    /**
    *   Save user to keychain
    */
    func saveUserToKeychain() {
        var userInformations: JSON = self.toPropertyList() as JSON
        var datas : NSData = userInformations.rawData()!
        NSUserDefaults.standardUserDefaults().setObject(datas, forKey: "user")
    }

    /**
    *   Add new session to user session
    *   :param: session The new session to add
    */
    func addNewSession(session: SMSession) {
        self.sessions?.addObject(session)
    }
    
    /**
    *
    *   Retrieve user from keychain (Class method)
    *   :returns:   SMUser  The stored user
    *
    */
    class func getUserFromKeychain() -> (SMUser?) {
        var savedData: NSData? = NSUserDefaults.standardUserDefaults().dataForKey("user")
        
        if(savedData != nil) {
            var jsonDatas: JSON = JSON(data: savedData!)
            return SMUser(userSettings: jsonDatas)
        }

        return nil
    }

    // Convert current user model into JSON object for keychain storage
    private func toPropertyList() -> JSON {
        var userJson: NSDictionary = [
            kId: self.id!,
            kFirstName: self.firstName!,
            kLastName: self.lastName!,
            kEmail: self.email!,
            kPicturePath: self.picturePath!,
            kWeight: self.weight!,
            kHeight: self.height!,
            kDoctor: self.doctor!,
            kBalance: self.balance!,
            kForceLeft: self.averageForceLeft!,
            kForceRight: self.averageForceRight!,
            kDiseaseDescription: self.diseaseDescription!,
            kSessions: self.sessionList()
        ]

        return JSON(userJson)
    }

    // Transform SMSession into NSarray in order to store it in keychain
    func sessionList() -> NSArray {

        var sessionsList = NSMutableArray()
        var tempSession: SMSession;

        for(var index = 0; index < self.sessions!.count; index++){
            tempSession = self.sessions!.objectAtIndex(index) as! SMSession
            sessionsList.addObject(tempSession.toPropertyList())
        }

        return sessionsList.copy() as! NSArray
    }
    
}