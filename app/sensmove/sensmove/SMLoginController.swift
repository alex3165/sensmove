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
        
        initializeUI()
        
    }
    
    override func didReceiveMemoryWarning() {
        super.didReceiveMemoryWarning()
        // Dispose of any resources that can be recreated.
    }
    
    func initializeUI(){
        var colorManager = SMColor()
        self.view.backgroundColor = colorManager.ligthGrey()
        
        brandLabel?.textColor = colorManager.orange()
        
    }
    
    @IBAction func validateCredentials(sender: AnyObject) {
        var id: NSString = identifier!.text
        var pass: NSString = password!.text
        
        let datasSingleton: SMData = SMData.sharedInstance
        let users: JSON = datasSingleton.getUsers()
        var usersArray = users.arrayValue
        
        var userIdentifier: NSString;
        var userPassword: NSString;
        
        for user in usersArray {
            userIdentifier = user["identifier"].stringValue
            userPassword = user["password"].stringValue
            
            if(userIdentifier == id && userPassword == pass) {
                println("Correct user and password navigate to home view")
                
                let storyboard = UIStoryboard(name: "Main", bundle: nil)
                let homeController = storyboard.instantiateViewControllerWithIdentifier("homeController") as! UIViewController
                self.navigationController?.pushViewController(homeController, animated: true)
            }
        }
    }
    
    
    func textFieldShouldReturn(textField: UITextField) -> Bool {
        self.view.endEditing(true)
        return false
    }
}