//
//  SMSession.swift
//  sensmove
//
//  Created by RIEUX Alexandre on 13/04/2015.
//  Copyright (c) 2015 ___alexprod___. All rights reserved.
//

import Foundation

class SMSession: NSObject {
    
    var startDate: NSDate = NSDate()
    var stopDate: NSDate = NSDate()
    
    var leftSole: SMSole
    var rightSole: SMSole

    init(leftSole: SMSole, rightSole: SMSole) {
        self.leftSole = leftSole
        self.rightSole = rightSole
        
        super.init()
        self.startSession()
    }
    
    private func startSession() {
        self.startDate = NSDate()
    }
    
    func stopSession() {
        
    }
    
    private func instantiateSoles() {
        
    }
}