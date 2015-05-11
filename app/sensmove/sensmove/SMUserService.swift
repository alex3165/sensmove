//
//  SMUserService.swift
//  sensmove
//
//  Created by RIEUX Alexandre on 20/04/2015.
//  Copyright (c) 2015 ___alexprod___. All rights reserved.
//

import Foundation

 typealias SMLoginSuccess = (userInformations: JSON) -> ()
 typealias SMLoginFailure = (error: NSException) -> ()

@objc class SMUserService: NSObject {

    var currentUser: SMUser? = SMUser.getUserFromKeychain()

    class var sharedInstance: SMUserService {
        struct Static {
            static let instance: SMUserService = SMUserService()
        }

        return Static.instance
    }

    func loginUserWithUserNameAndPassword(username: NSString, passwd: NSString, success: SMLoginSuccess, failure: SMLoginFailure) {
        var users = self.retrieveUsersFromDatasFile()

        for user in users {
            if user["username"].stringValue as String == username && passwd == user["password"].stringValue as String {
                success(userInformations: user)
                return;
            }
        }

        failure(error: NSException(name: "loginError", reason: "No user or password", userInfo: nil))
    }
    
    func setUser(user: SMUser) {
        self.currentUser = user
    }
    
    func saveUserToKeychain(){
        self.currentUser?.saveUserToKeychain()
    }
    
    func removeCurrentUser(){
        //self.currentUser?.removeUserFromKeychain()
    }
    
    func asUserInKeychain() -> Bool {
        return self.currentUser?.name != nil
    }

    private func retrieveUsersFromDatasFile() -> [JSON] {
        let datasSingleton: SMData = SMData.sharedInstance
        let users: JSON = datasSingleton.getUsers()
        var usersArray = users.arrayValue

        return usersArray
    }

}