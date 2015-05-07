//
//  SMUserTest.swift
//  sensmove
//
//  Created by RIEUX Alexandre on 20/04/2015.
//  Copyright (c) 2015 ___alexprod___. All rights reserved.
//

import UIKit
import XCTest

class SMUserTest: XCTestCase {

    var user: SMUser?
    
    override func setUp() {
        super.setUp()
        // Put setup code here. This method is called before the invocation of each test method in the class.
    }
    
    override func tearDown() {
        // Put teardown code here. This method is called after the invocation of each test method in the class.
        super.tearDown()
    }

    func testUserCreation(){
        var userJson: JSON = [
            "name": "Alexandre",
            "weight": 70,
            "height": 180,
            "doctor": "TestDoctor",
            "balance": "Great balance",
            "averageForceLeft": 120,
            "averageForceRight": 111
        ]
        self.user = SMUser(userSettings: userJson)
    }
    
    func testUserSaveToKeyChain(){
        self.testUserCreation()
        self.user?.saveUserToKeychain()
    }
    
    func testGetUserFromKeychain(){
        self.testUserSaveToKeyChain()
        let userTwo: SMUser = SMUser.getUserFromKeychain()!
    }

}
