//
//  SMHomeController.swift
//  sensmove
//
//  Created by RIEUX Alexandre on 12/04/2015.
//  Copyright (c) 2015 ___alexprod___. All rights reserved.
//

import UIKit

class SMHomeController: UIViewController {
    
    var currentSession: SMSession;
    
    required init(coder aDecoder: NSCoder) {
        fatalError("init(coder:) has not been implemented")
    }
    
    override func viewDidLoad() {
        super.viewDidLoad()

        var colorManager = SMColor()
        var grey = colorManager.ligthGrey()
        self.view.backgroundColor = grey
        
        initializeSession()
    }

    func initializeSession() {

        var rightSole = SMSole(isRight: true)
        var leftSole = SMSole(isRight: false)

        self.currentSession = SMSession(leftSole: leftSole, rightSole: rightSole)
    }

    override func didReceiveMemoryWarning() {
        super.didReceiveMemoryWarning()
        // Dispose of any resources that can be recreated.
    }

}

