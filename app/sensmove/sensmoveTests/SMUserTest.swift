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

    func getUserJson() -> JSON!{
        var userJson: JSON = [
            "username": "Alexandre",
            "weight": 70,
            "height": 180,
            "doctor": "TestDoctor",
            "balance": "Great balance",
            "averageForceLeft": 120,
            "averageForceRight": 111
        ]
        return userJson
    }
    
    func testUserCreation(){
        self.user = SMUser(userSettings: self.getUserJson())
    }
    
    func testUserSaveToKeyChain(){
        self.testUserCreation()
        self.user?.saveUserToKeychain()
    }
    
    func testGetUserFromKeychain(){
        self.testUserSaveToKeyChain()
        self.user = nil
        self.user = SMUser.getUserFromKeychain()
        var userOk: SMUser
        
        if(self.user != nil){
            userOk = self.user!
            assert(userOk.name!.isEqualToString("Alexandre"), "user name is correct so saving and retrieving from keychain is ok")
        }

    }
    
    func testUserServiceSave(){
        var userService: SMUserService = SMUserService.sharedInstance
        userService.setUser(SMUser(userSettings: self.getUserJson()))
        userService.saveUserToKeychain()
    }
    
    func testUserServiceGet(){
        var userServiceTwo: SMUserService = SMUserService.sharedInstance
        if(userServiceTwo.currentUser != nil){
            assert(userServiceTwo.currentUser!.name!.isEqualToString("Alexandre"), "User is correctely retrieved")
        }
        
    }
    
    func testRemoveUserFromKeychain(){
        NSUserDefaults.standardUserDefaults().removeObjectForKey("user")
    }

}
