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

    var currentSession: SMSession?;
    
    required init(coder aDecoder: NSCoder) {
        super.init(coder: aDecoder)
        
        var rightSole = SMSole(isRight: true)
        var leftSole = SMSole(isRight: false)

        self.currentSession = SMSession(leftSole: leftSole, rightSole: rightSole)
    }
    
    override func viewDidLoad() {
        super.viewDidLoad()

        let locations: [Float] = [0.2, 1]
        var smGradient: SMGradient = SMGradient(locations: locations)
        smGradient.setFrameFromRect(self.loaderView.bounds)
        smGradient.setCRadius(95)

        self.loaderView.layer.insertSublayer(smGradient, atIndex: 0)

        let colorManager: SMColor = SMColor()
        self.view.backgroundColor = colorManager.ligthGrey()
    }

    override func didReceiveMemoryWarning() {
        super.didReceiveMemoryWarning()
        // Dispose of any resources that can be recreated.
    }



}

