//
//  SMViewController.swift
//  sensmove
//
//  Created by RIEUX Alexandre on 06/04/2015.
//  Copyright (c) 2015 ___alexprod___. All rights reserved.
//

import UIKit
import <SMColor>

typealias SMViewController = UIViewController

extension SMViewController {

    convenience init() {
        self.view.backgroundColor = SMColor.lightGrey()
    }

}
