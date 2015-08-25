//
//  SMHomeController.swift
//  sensmove
//
//  Created by RIEUX Alexandre on 12/04/2015.
//  Copyright (c) 2015 ___alexprod___. All rights reserved.
//

import UIKit

enum NotificationText: String {
    case searching = "Recherche de la semelle"
    case found = "Connecté à la semelle"
}

class SMHomeController: UIViewController {
    
    @IBOutlet weak var loaderView: UIView!
    @IBOutlet weak var textNotification: UILabel!
    @IBOutlet weak var notificationView: UIView!

    var finalNotificationFrame: CGRect?
    
    required init(coder aDecoder: NSCoder) {
        super.init(coder: aDecoder)
    }
    
    override func viewDidLoad() {
        super.viewDidLoad()

        self.createStartButton()
        self.notificationView.hidden = true
//        let actualFrame = self.notificationView.frame
//        self.finalNotificationFrame = CGRectMake(actualFrame.origin.x, actualFrame.origin.y - actualFrame.size.height, actualFrame.size.width, actualFrame.size.height)
    }

    /**
    *   Graphicaly create main button
    */
    func createStartButton() {
        let locations: [Float] = [0.2, 1]
        var smGradient: SMGradient = SMGradient(locations: locations)
        smGradient.setFrameFromRect(self.loaderView.bounds)
        smGradient.setCRadius(95)

        self.loaderView.layer.insertSublayer(smGradient, atIndex: 0)
        self.view.backgroundColor = SMColor.ligthGrey()
    }

    override func didReceiveMemoryWarning() {
        super.didReceiveMemoryWarning()
    }

    /**
    *   Listen for tap action on main start button
    *   :param: sender  Gesture type
    */
    @IBAction func handleTap(sender: UITapGestureRecognizer) {
        if sender.state == .Ended {
            let btDiscovery: SMBLEDiscovery = btDiscoverySharedInstance
            self.textNotification.text = NotificationText.searching.rawValue
            self.notificationView.hidden = false

            RACObserve(btDiscovery, "bleService").subscribeNext({ (bleService) -> Void in
                if let btService = bleService as? SMBLEService {
                    RACObserve(btService, "isConnectedToDevice").subscribeNextAs({ (isConnectedToDevice: Bool) -> () in
                        if isConnectedToDevice {
                            self.textNotification.text = NotificationText.found.rawValue
                            
                            let trackController: UIViewController = self.storyboard?.instantiateViewControllerWithIdentifier("navSession") as! UIViewController
                            self.navigationController?.presentViewController(trackController, animated: true, completion: nil)
                        }
                    })
                }
            })
        }
    }

}

