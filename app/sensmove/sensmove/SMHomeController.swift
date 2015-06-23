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
    
    required init(coder aDecoder: NSCoder) {
        super.init(coder: aDecoder)
    }
    
    override func viewDidLoad() {
        super.viewDidLoad()
        
        self.createStartButton()
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
            let trackController: UIViewController = self.storyboard?.instantiateViewControllerWithIdentifier("trackView") as! UIViewController
            self.navigationController?.pushViewController(trackController, animated: true)
        }
    }

}

