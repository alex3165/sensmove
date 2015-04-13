//
//  SMSession.swift
//  sensmove
//
//  Created by RIEUX Alexandre on 13/04/2015.
//  Copyright (c) 2015 ___alexprod___. All rights reserved.
//

import Foundation

class SMSession {
    
    var startDate: NSDate = NSDate()
    var stopDate: NSDate = NSDate()
    
    var leftSole: SMSole
    var rightSole: SMSole

    init(leftSole: SMSole, rightSole: SMSole) {
        self.leftSole = leftSole
        self.rightSole = rightSole
        
        startSession()
    }
    
    private func startSession() {
        self.startDate = NSDate()
    }
    
    func stopSession() {
        
    }
    
    private func instantiateSoles() {
        
    }
}