//
//  SMPresentationController.swift
//  sensmove
//
//  Created by alexandre on 20/08/2015.
//  Copyright (c) 2015 ___alexprod___. All rights reserved.
//

import UIKit

class SMPresentationController: UIViewController {
    override func viewDidLoad() {
        super.viewDidLoad()
        
    }
    
    override func didReceiveMemoryWarning() {
        super.didReceiveMemoryWarning()
    }
    
    @IBAction func startApplication(sender: UIButton) {
        NSUserDefaults.standardUserDefaults().setBool(true, forKey: "hasUnlockedPresentation")
        NSUserDefaults.standardUserDefaults().synchronize()
        
        let storyboard = UIStoryboard(name: "Main", bundle: nil)
        let homeController: UIViewController = storyboard.instantiateViewControllerWithIdentifier("sideMenuController") as! UIViewController

        self.presentViewController(homeController, animated: true) { () -> Void in
            println("Redirection to home controller")
        }
    }
}
