//
//  SMUserService.swift
//  sensmove
//
//  Created by RIEUX Alexandre on 20/04/2015.
//  Copyright (c) 2015 ___alexprod___. All rights reserved.
//

import Foundation

typealias SMLoginSuccess = (userInformations: NSDictionary) -> ()
typealias SMLoginFailure = (error: NSException) -> ()

class SMUserService: NSObject {
    
    var currentUser: SMUser = SMUser.getUserFromKeychain()!
    
    class var sharedInstance: SMUserService {
        struct Static {
            static let instance: SMUserService = SMUserService()
        }

        return Static.instance
    }
    
    func loginUserWithUserNameAndPassword(username: NSString, passwd: NSString, success: SMLoginSuccess, failure: SMLoginFailure) {
        var users = self.retrieveUsersFromDatasFile()
        
        for user in users {
            if user["name"].stringValue as String == username && passwd == user["password"].stringValue as String {
                success(userInformations: user.dictionaryValue as! NSDictionary)
            }
        }
        
        failure(error: NSException(name: "loginError", reason: "No user or password", userInfo: nil))
    }
    
    private func retrieveUsersFromDatasFile() -> [JSON] {
        let datasSingleton: SMData = SMData.sharedInstance
        let users: JSON = datasSingleton.getUsers()
        var usersArray = users.arrayValue
        
        return usersArray
    }
    
}