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
        // Dispose of any resources that can be recreated.
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
        
        self.userService.loginUserWithUserNameAndPassword(username, passwd: pass, success: { (informations) -> () in
                self.userService.setUser(SMUser(userSettings: informations))
                self.userService.currentUser?.saveUserToKeychain()
            
                self.redirectToView("sideMenuController")

            }, failure: { (error) -> () in
                self.badCredentials?.hidden = false
                var timer = NSTimer.scheduledTimerWithTimeInterval(2, target: self, selector: Selector("onHideBadCredentials"), userInfo: nil, repeats: false)
        })
    }
    
    func redirectToView(view: String) {
        let storyboard = UIStoryboard(name: "Main", bundle: nil)
        let newController: UIViewController = storyboard.instantiateViewControllerWithIdentifier(view) as! UIViewController

        self.presentViewController(newController, animated: true) { () -> Void in
            println("present new view controller done")
        }
    }

    func onHideBadCredentials() {
        self.badCredentials?.hidden = true
    }
    
    
    func textFieldShouldReturn(textField: UITextField) -> Bool {
        self.view.endEditing(true)
        return false
    }
}