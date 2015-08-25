//
//  SMHomeController.swift
//  sensmove
//
//  Created by RIEUX Alexandre on 12/04/2015.
//  Copyright (c) 2015 ___alexprod___. All rights reserved.
//

import UIKit



class SMHomeController: UIViewController {
    
    @IBOutlet weak var loaderView: UIView!
    @IBOutlet weak var textNotification: UILabel!
    @IBOutlet weak var notificationView: UIView!
    @IBOutlet weak var buttonValue: UILabel!
    
    dynamic var isSearching: Bool = false
    
    var searchLoader: LFTPulseAnimation!
    
    enum NotificationText: String {
        case searching = "Recherche de la semelle"
        case found = "Connecté à la semelle"
    }

    required init(coder aDecoder: NSCoder) {
        super.init(coder: aDecoder)
    }
    
    override func viewDidLoad() {
        super.viewDidLoad()

        self.createStartButton()
        self.notificationView.hidden = true
        
        self.searchLoader = LFTPulseAnimation(repeatCount: Float.infinity, radius: 160, position: CGPointMake(self.view.center.x, self.view.center.y))
        self.searchLoader.animationDuration = NSTimeInterval(2)
        self.searchLoader.backgroundColor = SMColor.orange().CGColor
        self.view.layer.insertSublayer(self.searchLoader, below: self.loaderView.layer)
        
        
        
        RACObserve(self, "isSearching").subscribeNextAs { (isSearching: Bool) -> () in
            
            if isSearching {
                self.searchLoader.startAnimation()
            } else {
                self.searchLoader.stopAnimation()
            }

            self.textNotification.text = NotificationText.searching.rawValue
            self.notificationView.hidden = !isSearching
            
            self.buttonValue.text = isSearching ? "STOP" : "START"
        }
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

            self.isSearching = !self.isSearching

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

