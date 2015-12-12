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
    }
    
    override func tearDown() {
        super.tearDown()
    }

    func getUserJson() -> JSON!{
        let userJson: JSON = [
            "id": 32,
            "firstname": "Addison",
            "lastname": "Richards",
            "email": "addison.richards26@example.com",
            "password": "funfun",
            "weight": 64,
            "height": 172,
            "doctor": "TestDoctor",
            "balance": "Great balance",
            "averageForceLeft": 120,
            "averageForceRight": 111,
            "diseaseDescription": "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras egestas auctor justo vel mattis.",
            "sessions": []
        ]
        return userJson
    }
    
    func testUserCreation() {
        self.user = SMUser(userSettings: self.getUserJson())
    }
    
    func testUserSaveToKeyChain() {
        self.testUserCreation()
        self.user?.saveUserToKeychain()
    }
    
    func testGetUserFromKeychain() {
        self.testUserSaveToKeyChain()
        self.user = nil
        self.user = SMUser.getUserFromKeychain()

        if let localUser = self.user {
            XCTAssert(localUser.firstName!.isEqualToString("Addison"), "Save and fetch user from keychain Ok")
        }else {
            XCTAssert(false, "Cannot fetch user from keychain")
        }

    }

    func testUserServiceSave() {
        let userService: SMUserService = SMUserService.sharedInstance
        userService.setUser(SMUser(userSettings: self.getUserJson()))
        userService.saveUserToKeychain()
    }
    
    func testUserServiceGet() {
        if let currentUser = SMUserService.sharedInstance.currentUser {
            XCTAssert(currentUser.firstName!.isEqualToString("Addison"), "User is correctely retrieved")
        }
        
    }
    
    func testRemoveUserFromKeychain() {
        //SMUserService.sharedInstance.currentUser?.removeObjectToKeychain()
    }

}
