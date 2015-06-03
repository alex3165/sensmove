//
//  SMData.swift
//  sensmove
//
//  Created by RIEUX Alexandre on 11/04/2015.
//  Copyright (c) 2015 ___alexprod___. All rights reserved.
//

import Foundation

typealias SMParseFileSuccess = (datas: NSData) -> ()
typealias SMParseFileFailure = (error: NSException) -> ()

class SMData {

    var userDatas: JSON = []
    var sensorsDatas: JSON = []
    
    func initializeUsers(json: JSON) {
        self.userDatas = json
    }

    func initializeSensors(json: JSON) -> (JSON) {
        self.sensorsDatas = json
        return self.sensorsDatas
    }

    func getUsers() -> JSON {
        return self.userDatas["user"]
    }
    
    func getSensors() -> JSON {
        return self.sensorsDatas
    }

    func getDatasFromFile(filePath: NSString, success: SMParseFileSuccess, failure: SMParseFileFailure) {
        let file = NSBundle.mainBundle().pathForResource(filePath as String, ofType: "json")

        if(file != nil) {
            success(datas: NSData(contentsOfFile: file!)!)
        }else {
            failure(error: NSException(name: "ParseError", reason: "Unable to parse file, path not found in main bundle", userInfo: nil))
        }
    }
    
    class var sharedInstance: SMData {
            struct Static {
                static let instance: SMData = SMData()
            }
            return Static.instance
    }
    
}