//
//  SMGradient.swift
//  sensmove
//
//  Created by RIEUX Alexandre on 20/05/2015.
//  Copyright (c) 2015 ___alexprod___. All rights reserved.
//

import UIKit


/**
*
*   Custom layer that follow sensmove design pattern with correct gradient and customizable border radius
*
*/
class SMGradient: CAGradientLayer {
    
    
    init(locations: NSArray) {
        super.init()
        
        self.colors = [SMColor.orange().CGColor, SMColor.red().CGColor]
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
