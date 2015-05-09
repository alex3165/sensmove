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
    
    required init(coder aDecoder: NSCoder) {
        super.init(coder: aDecoder)
    }
    
    override func viewDidLoad() {
        super.viewDidLoad()
        
        self.identifier?.delegate = self
        self.password?.delegate = self
        self.checkUserFromKeychain()
        initializeUI()
    }
    
    override func didReceiveMemoryWarning() {
        super.didReceiveMemoryWarning()
        // Dispose of any resources that can be recreated.
    }
    
    private func checkUserFromKeychain() {
        var user: SMUser? = SMUserService.sharedInstance.currentUser
        if(user is SMUser!) {
            self.redirectToView("sideMenuController")
        }
    }
    
    func initializeUI(){
        var colorManager = SMColor()
        self.view.backgroundColor = colorManager.ligthGrey()
        self.badCredentials?.textColor = colorManager.red()
        brandLabel?.textColor = colorManager.orange()
    }
    
    @IBAction func validateCredentials(sender: AnyObject) {
        var username: NSString = identifier!.text
        var pass: NSString = password!.text
        
        var userService = SMUserService.sharedInstance
        
        userService.loginUserWithUserNameAndPassword(username, passwd: pass, success: { (informations) -> () in
            
            var user: SMUser = SMUser(userSettings: informations)
                user.saveUserToKeychain()

                self.redirectToView("sideMenuController")
            }, failure: { (error) -> () in
                self.badCredentials?.hidden = false
                var timer = NSTimer.scheduledTimerWithTimeInterval(2, target: self, selector: Selector("onHideBadCredentials"), userInfo: nil, repeats: false)
        })
    }
    
    private func redirectToView(view: NSString) {
        let storyboard = UIStoryboard(name: "Main", bundle: nil)
        let newController = storyboard.instantiateViewControllerWithIdentifier(view as String) as! UIViewController
        self.showViewController(newController, sender: newController)
    }

    func onHideBadCredentials() {
        self.badCredentials?.hidden = true
    }
    
    
    func textFieldShouldReturn(textField: UITextField) -> Bool {
        self.view.endEditing(true)
        return false
    }
}