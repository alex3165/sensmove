//
//  SMConnexionNotification.swift
//  sensmove
//
//  Created by alexandre on 19/08/2015.
//  Copyright (c) 2015 ___alexprod___. All rights reserved.
//

import UIKit

class SMConnexionNotification: UIView {

    @IBOutlet weak var notificationLabel: UILabel!

    required init?(coder aDecoder: NSCoder) {
        super.init(coder: aDecoder)
        
        self.hideNotification()
    }
    
    func hideNotification() {
        self.notificationLabel.hidden = true
    }
    
    func setNotification(text: String) {
        self.notificationLabel.hidden = false
        self.notificationLabel.text = text
    }
}
