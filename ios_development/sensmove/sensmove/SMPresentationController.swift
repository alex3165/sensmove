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
    @IBOutlet weak var animationView: UIView!
    
    var initialFrame: CGRect!
    var finalFrame: CGRect!
    
    override func viewDidLoad() {
        super.viewDidLoad()

        self.startButton.backgroundColor = SMColor.green()
        self.startButton.setTitleColor(SMColor.whiteColor(), forState: UIControlState.Normal)
        self.startButton.layer.cornerRadius = 6
        
        self.mainTitle.textColor = SMColor.orange()
        
        self.animationView.backgroundColor = SMColor.ligthGrey()
        self.animationView.layer.cornerRadius = 50
        self.animationView.contentMode = UIViewContentMode.Center
        self.animationView.center = CGPoint(x: self.animationView.frame.size.width/2, y: self.animationView.frame.size.height/2)
        self.finalFrame = self.animationView.frame
        self.initialFrame = CGRectMake(self.finalFrame.origin.x, self.finalFrame.origin.y, 0, 0)
        self.animationView.frame = self.initialFrame
    }
    
    override func didReceiveMemoryWarning() {
        super.didReceiveMemoryWarning()
    }
    
    @IBAction func onTapLogo(sender: UIButton) {
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
