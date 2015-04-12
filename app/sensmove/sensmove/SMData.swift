//
//  SMData.swift
//  sensmove
//
//  Created by RIEUX Alexandre on 11/04/2015.
//  Copyright (c) 2015 ___alexprod___. All rights reserved.
//

import Foundation

class SMData {

    var jsonDatas: JSON = []
    
    func initializeDatas(json: JSON) {
        self.jsonDatas = json
    }

    func getUsers() -> JSON {
        return self.jsonDatas["user"]
    }
    
    class var sharedInstance: SMData {
            struct Static {
                static let instance: SMData = SMData()
            }
            return Static.instance
    }
    
}