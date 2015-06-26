//
//  SMResultController.swift
//  sensmove
//
//  Created by RIEUX Alexandre on 23/06/2015.
//  Copyright (c) 2015 ___alexprod___. All rights reserved.
//

import UIKit

class SMResultController: UIViewController {

    @IBOutlet weak var duration: UILabel?
    @IBOutlet weak var jumps: UILabel?
    @IBOutlet weak var walks: UILabel?
    @IBOutlet weak var balance: UILabel?
    @IBOutlet weak var averageLeftForce: UILabel?
    @IBOutlet weak var averageRightForce: UILabel?
    
    @IBOutlet weak var sessionName: UITextField?
    @IBOutlet weak var activityType: UITextField?
    @IBOutlet weak var sessionComment: UITextField?
    
    @IBOutlet weak var saveSession: UIButton?
    @IBOutlet weak var deleteSession: UIButton?

    var trackSessionService: SMTrackSessionService?
    
    override func viewDidLoad() {
        super.viewDidLoad()
        
        self.initializeUI()
        self.trackSessionService = SMTrackSessionService.sharedInstance
    }

    override func didReceiveMemoryWarning() {
        super.didReceiveMemoryWarning()
        // Dispose of any resources that can be recreated.
    }
    
    func initializeUI() {
        self.saveSession?.backgroundColor = SMColor.green()
        self.saveSession?.setTitleColor(SMColor.whiteColor(), forState: UIControlState.Normal)
        
        self.deleteSession?.backgroundColor = SMColor.red()
        self.deleteSession?.setTitleColor(SMColor.whiteColor(), forState: UIControlState.Normal)
        
        self.duration?.textColor = SMColor.orange()
        self.jumps?.textColor = SMColor.orange()
        self.walks?.textColor = SMColor.orange()
        self.balance?.textColor = SMColor.orange()
        self.averageLeftForce?.textColor = SMColor.orange()
        self.averageRightForce?.textColor = SMColor.orange()
    }

    @IBAction func saveSessionAction(sender: AnyObject) {

        if let sessionTextName = self.sessionName?.text {
            if count(sessionTextName) > 0 {
                self.trackSessionService?.currentSession?.name = sessionTextName
            }
        }

        if let descriptionText = self.sessionComment?.text {
            if count(descriptionText) > 0 {
                self.trackSessionService?.currentSession?.sessionComment = descriptionText
            }
        }
        
        if let activityText = self.sessionComment?.text {
            if count(activityText) > 0 {
                self.trackSessionService?.currentSession?.activity = activityText
            }
        }

        self.trackSessionService?.saveCurrentSession()
        self.redirectToHomeView()
    }

    @IBAction func deleteSessionAction(sender: AnyObject) {
        self.trackSessionService?.deleteCurrentSession()
        self.redirectToHomeView()
    }
    
    func redirectToHomeView() {
        let sidemenuController: UIViewController = self.storyboard?.instantiateViewControllerWithIdentifier("sideMenuController") as! UIViewController

        let navigationController: UINavigationController = UINavigationController(rootViewController: sidemenuController)
        self.presentViewController(navigationController, animated: true, completion: nil)
    }
    /*
    // MARK: - Navigation

    // In a storyboard-based application, you will often want to do a little preparation before navigation
    override func prepareForSegue(segue: UIStoryboardSegue, sender: AnyObject?) {
        // Get the new view controller using segue.destinationViewController.
        // Pass the selected object to the new view controller.
    }
    */

}
