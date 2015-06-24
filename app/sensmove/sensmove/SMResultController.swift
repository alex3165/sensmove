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

        self.trackSessionService = SMTrackSessionService.sharedInstance
    }

    override func didReceiveMemoryWarning() {
        super.didReceiveMemoryWarning()
        // Dispose of any resources that can be recreated.
    }
    

    @IBAction func saveSessionAction(sender: AnyObject) {
        self.trackSessionService?.saveCurrentSession()
        self.redirectToHomeView()
    }

    @IBAction func deleteSessionAction(sender: AnyObject) {
        self.trackSessionService?.deleteCurrentSession()
        self.redirectToHomeView()
    }
    
    
    func redirectToHomeView() {
        let homeController: UIViewController = self.storyboard?.instantiateViewControllerWithIdentifier("homeController") as! UIViewController
        self.navigationController?.presentViewController(homeController, animated: true, completion: nil)
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
