//
//  SMLoginController.swift
//  sensmove
//
//  Created by RIEUX Alexandre on 06/04/2015.
//  Copyright (c) 2015 ___alexprod___. All rights reserved.
//

import UIKit

class SMLoginController: UIViewController, UITextFieldDelegate {
    
    @IBOutlet weak var brandLabel: UILabel?
    @IBOutlet weak var badCredentials: UILabel?
    
    @IBOutlet weak var identifier: UITextField?
    @IBOutlet weak var password: UITextField?
    
    @IBOutlet weak var validation: UIButton?
    
    var userService: SMUserService
    
    required init(coder aDecoder: NSCoder) {
        self.userService = SMUserService.sharedInstance
        super.init(coder: aDecoder)
    }
    
    override func viewDidLoad() {
        super.viewDidLoad()
        
        self.identifier?.delegate = self
        self.password?.delegate = self
        initializeUI()
    }
    
    override func didReceiveMemoryWarning() {
        super.didReceiveMemoryWarning()
    }

    /**
    *   Initialize login controller UI
    */
    func initializeUI(){
        self.view.backgroundColor = SMColor.ligthGrey()
        self.badCredentials?.textColor = SMColor.red()
        brandLabel?.textColor = SMColor.orange()
    }
    
    /**
    *   Action triggered when use tap on main home button
    *   :param: 
    */
    @IBAction func validateCredentials(sender: AnyObject) {
        var username: NSString = identifier!.text
        var pass: NSString = password!.text
        
        /// Try login user with login and password entered
        self.userService.loginUserWithUserNameAndPassword(username, passwd: pass, success: { (informations) -> () in
                var userToSave: SMUser = SMUser(userSettings: informations)
                self.userService.setUser(userToSave)
                self.userService.saveUserToKeychain()

                self.redirectToView("sideMenuController")

            }, failure: { (error) -> () in
                self.badCredentials?.hidden = false
                /// Trigger timer that hide failure message in 2 seconds
                var timer = NSTimer.scheduledTimerWithTimeInterval(2, target: self, selector: Selector("onHideBadCredentials"), userInfo: nil, repeats: false)
        })
    }
    
    /**
    *   Redirect to the given view
    *   :param: view The view to redirect at
    */
    func redirectToView(view: String) {
        let storyboard = UIStoryboard(name: "Main", bundle: nil)
        let newController: UIViewController = storyboard.instantiateViewControllerWithIdentifier(view) as! UIViewController

        self.presentViewController(newController, animated: true) { () -> Void in
            println("present new view controller done")
        }
    }

    /**
    *   Hide failure message
    */
    func onHideBadCredentials() {
        self.badCredentials?.hidden = true
    }
    
    
    func textFieldShouldReturn(textField: UITextField) -> Bool {
        self.view.endEditing(true)
        return false
    }
}