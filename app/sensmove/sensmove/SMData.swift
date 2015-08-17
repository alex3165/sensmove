//
//  SMData.swift
//  sensmove
//
//  Created by RIEUX Alexandre on 11/04/2015.
//  Copyright (c) 2015 ___alexprod___. All rights reserved.
//

import Foundation

/// Define closure type when succeed parsing file
typealias SMParseFileSuccess = (datas: NSData) -> ()

/// Define closure type when fail parsing file
typealias SMParseFileFailure = (error: NSException) -> ()

class SMData {

    var userDatas: JSON = []
    var sensorsDatas: JSON = []
    
    /// Singleton instance of SMData
    class var sharedInstance: SMData {
        
        struct Static {
            static let instance: SMData = SMData()
        }
        
        return Static.instance
    }

    /// Initialize users JSON object from given JSON and return it
    func initializeUsers(json: JSON) {
        self.userDatas = json
    }

    /// Initialize sensors JSON object from given JSON and return it
    func initializeSensors(json: JSON) -> (JSON) {
        self.sensorsDatas = json
        return self.sensorsDatas
    }

    /// Return all users
    func getUsers() -> JSON {
        return self.userDatas["user"]
    }
    
    /// Return all sensors
    func getSensors() -> JSON {
        return self.sensorsDatas
    }

    /// Retrieve datas from given file then execute SMParseFileSuccess or SMParseFileFailure closure
    func getDatasFromFile(filePath: NSString, success: SMParseFileSuccess, failure: SMParseFileFailure) {
        let file = NSBundle.mainBundle().pathForResource(filePath as String, ofType: "json")

        if(file != nil) {
            success(datas: NSData(contentsOfFile: file!)!)
        }else {
            failure(error: NSException(name: "ParseError", reason: "Unable to parse file, path not found in main bundle", userInfo: nil))
        }
    }
    
}