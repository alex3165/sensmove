//
//  SMHomeController.swift
//  sensmove
//
//  Created by RIEUX Alexandre on 12/04/2015.
//  Copyright (c) 2015 ___alexprod___. All rights reserved.
//

import UIKit

class SMHomeController: UIViewController {
    
    override func viewDidLoad() {
        super.viewDidLoad()

        var colorManager = SMColor();
        var grey = colorManager.ligthGrey();
        self.view.backgroundColor = grey;
    }
    
    override func didReceiveMemoryWarning() {
        super.didReceiveMemoryWarning()
        // Dispose of any resources that can be recreated.
    }

}

