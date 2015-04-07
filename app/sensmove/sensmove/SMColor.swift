//
//  SMColors.swift
//  sensmove
//
//  Created by RIEUX Alexandre on 06/04/2015.
//  Copyright (c) 2015 ___alexprod___. All rights reserved.
//
import UIKit
typealias SMColor = UIColor

extension SMColor{
    
    convenience init(red: Int, green: Int, blue: Int)
    {
        let newRed = CGFloat(red)/255
        let newGreen = CGFloat(green)/255
        let newBlue = CGFloat(blue)/255
        
        self.init(red: newRed, green: newGreen, blue: newBlue, alpha: 1.0)
    }
    
    func orange() -> SMColor {
        return SMColor(red: 244, green: 85, blue: 28);
    }

    func ligthGrey() -> SMColor {
        return SMColor(red: 240, green: 240, blue: 240);
    }
    
    func middleGrey() -> SMColor {
        return SMColor(red: 136, green: 136, blue: 136);
    }
    
    func darkGrey() -> SMColor {
        return SMColor(red: 51, green: 51, blue: 51);
    }
    
    func red() -> SMColor {
        return SMColor(red: 233, green: 74, blue: 60);
    }
    
    func blue() -> SMColor {
        return SMColor(red: 83, green: 109, blue: 254);
    }
    
    func green() -> SMColor {
        return SMColor(red: 41, green: 212, blue: 125);
    }
}
