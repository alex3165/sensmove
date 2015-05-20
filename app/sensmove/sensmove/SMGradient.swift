//
//  SMGradient.swift
//  sensmove
//
//  Created by RIEUX Alexandre on 20/05/2015.
//  Copyright (c) 2015 ___alexprod___. All rights reserved.
//

import UIKit

class SMGradient: CAGradientLayer {
    init(locations: NSArray) {
        super.init()
        let colorManager: SMColor = SMColor()
        
        self.colors = [colorManager.orange().CGColor, colorManager.red().CGColor]
        self.locations = locations as [AnyObject]

    }
    
    func setFrameFromRect(viewBounds: CGRect) {
        self.frame = viewBounds
    }
    
    func setCRadius(rad: Float) {
        self.cornerRadius = CGFloat(rad)
    }

    required init(coder aDecoder: NSCoder) {
        fatalError("init(coder:) has not been implemented")
    }
}
