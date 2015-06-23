//
//  SMUserService.swift
//  sensmove
//
//  Created by RIEUX Alexandre on 20/04/2015.
//  Copyright (c) 2015 ___alexprod___. All rights reserved.
//

import Foundation

// Login success closure type definition
typealias SMLoginSuccess = (userInformations: JSON) -> ()
// Login failure closure type definition
typealias SMLoginFailure = (error: NSException) -> ()

class SMUserService: NSObject {

    // The current user retrieved from keychain storage or nil if not exist
    var currentUser: SMUser? = SMUser.getUserFromKeychain()

    // Singleton instance of SMUserService
    class var sharedInstance: SMUserService {
        struct Static {
            static let instance: SMUserService = SMUserService()
        }

        return Static.instance
    }

    /** 
    * 
    * Description : Login method with username and password, pass SMLoginSuccess block of login succeed
    * or SMLoginFailure if not.
    *
    */
    func loginUserWithUserNameAndPassword(username: NSString, passwd: NSString, success: SMLoginSuccess, failure: SMLoginFailure) {
        var users = self.retrieveUsersFromDatasFile()

        for user in users {
            if user[kFirstName].stringValue as String == username && passwd == user["password"].stringValue as String {
                success(userInformations: user)
                return;
            }
        }

        failure(error: NSException(name: "loginError", reason: "No user or password", userInfo: nil))
    }
    
    // Setter for currentUser
    func setUser(user: SMUser) {
        self.currentUser = user
    }
    
    // Add new session to the currentUser then save to keychain
    func addSessionToCurrentUser(session: SMSession) {
        self.currentUser?.sessions?.addObject(session)
        self.saveUserToKeychain()
    }
    
    // Save current user state to keychain
    func saveUserToKeychain(){
        self.currentUser?.saveUserToKeychain()
    }
    
    // Check if firstName of currentUser is set
    func asUserInKeychain() -> Bool {
        return self.currentUser?.firstName != nil
    }

    // Return every users stored in SMData file
    private func retrieveUsersFromDatasFile() -> [JSON] {
        let datasSingleton: SMData = SMData.sharedInstance
        let users: JSON = datasSingleton.getUsers()
        var usersArray: Array = users.arrayValue

        return usersArray
    }

}