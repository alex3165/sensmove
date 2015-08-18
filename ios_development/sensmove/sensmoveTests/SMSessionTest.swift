//
//  SMSessionTest.swift
//  sensmove
//
//  Created by RIEUX Alexandre on 24/06/2015.
//  Copyright (c) 2015 ___alexprod___. All rights reserved.
//

import UIKit
import XCTest

class SMSessionTest: XCTestCase {

    override func setUp() {
        super.setUp()
        // Put setup code here. This method is called before the invocation of each test method in the class.
    }
    
    override func tearDown() {
        // Put teardown code here. This method is called after the invocation of each test method in the class.
        super.tearDown()
    }

    func testCreateSession() {
        let sessionService = SMTrackSessionService.sharedInstance

        sessionService.createNewSession()
        if let name: String = sessionService.currentSession?.name as? String {
            XCTAssert(name == "new_session", "Session has been created with it's default name")
        }
    }
    
    func testCreateStopAndSaveSession() {
        self.testCreateSession()
        
        let sessionService = SMTrackSessionService.sharedInstance
        
        sessionService.stopCurrentSession(NSTimeInterval(3.10))
        sessionService.saveCurrentSession()
        XCTAssert(sessionService.currentSession == nil, "Session correctly deleted")
    }
    
    func testCreateAndRetrieveSessions() {
        let userService: SMUserService = SMUserService.sharedInstance
        
        let initialNumberOfSessions = userService.currentUser?.sessions.count

        self.testCreateStopAndSaveSession() // Create and stop session
        
        XCTAssert(SMUserService.sharedInstance.currentUser?.sessions.count == initialNumberOfSessions! + 1, "Session correctly stored")
    }
    
    func testDeleteLastSession() {
        self.testCreateStopAndSaveSession()
        
        let userService: SMUserService = SMUserService.sharedInstance
        let initialNumberOfSessions = userService.currentUser?.sessions.count

        userService.currentUser?.sessions.removeLast()
        userService.currentUser?.saveUserToKeychain()
        
        XCTAssert(userService.currentUser?.sessions.count == initialNumberOfSessions! - 1, "Session correctly deleted")
    }
    
    func testDeleteAllSession() {
        self.testCreateStopAndSaveSession()
        self.testCreateStopAndSaveSession()

        let userService: SMUserService = SMUserService.sharedInstance
        userService.currentUser?.sessions.removeAll(keepCapacity: true)
        userService.currentUser?.saveUserToKeychain()

        XCTAssert(userService.currentUser?.sessions.count == 0, "All sessions correctly deleted")
        
    }

}
