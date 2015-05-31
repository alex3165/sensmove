//
//  SMTrackSessionService.swift
//  sensmove
//
//  Created by RIEUX Alexandre on 31/05/2015.
//  Copyright (c) 2015 ___alexprod___. All rights reserved.
//

import UIKit

class SMTrackSessionService: NSObject {

    var currentSession: SMSession?
    
    class var sharedInstance: SMTrackSessionService {
        struct Static {
            static let instance: SMTrackSessionService = SMTrackSessionService()
        }
        return Static.instance
    }
    
    func createNewSession(){
        self.currentSession = SMSession(userInfos: SMUserService.sharedInstance.currentUser!)
    }
}
