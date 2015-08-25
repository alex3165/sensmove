//
//  SMPresentationController.swift
//  sensmove
//
//  Created by alexandre on 20/08/2015.
//  Copyright (c) 2015 ___alexprod___. All rights reserved.
//

import UIKit

class SMPresentationController: UIViewController {
    
    @IBOutlet weak var startButton: UIButton!
    @IBOutlet weak var mainTitle: UILabel!
    @IBOutlet weak var logoButton: UIButton!
    
    weak var halo: LFTPulseAnimation!
    
    override func viewDidLoad() {
        super.viewDidLoad()

        self.startButton.backgroundColor = SMColor.green()
        self.startButton.setTitleColor(SMColor.whiteColor(), forState: UIControlState.Normal)
        self.startButton.layer.cornerRadius = 6
        
        self.mainTitle.textColor = SMColor.orange()

    }

    override func didReceiveMemoryWarning() {
        super.didReceiveMemoryWarning()
    }
    
    @IBAction func onTapLogo(sender: UIButton) {
        halo = LFTPulseAnimation(repeatCount: 1, radius: 100, position: CGPointMake(self.view.center.x, self.logoButton.center.y))
        halo.animationDuration =  NSTimeInterval(1)
        halo.backgroundColor = SMColor.orange().CGColor
        self.view.layer.insertSublayer(halo, below: self.logoButton.layer)
        // TODO : animate ellipse at touch event
//        UIView.animateWithDuration(NSTimeInterval(0.5), animations: { () -> Void in
//            self.animationView.frame = self.finalFrame
//        })
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
