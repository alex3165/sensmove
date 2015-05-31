//
//  SMSessionViewCell.swift
//  sensmove
//
//  Created by RIEUX Alexandre on 30/05/2015.
//  Copyright (c) 2015 ___alexprod___. All rights reserved.
//

import UIKit

class SMSessionViewCell: UITableViewCell {

    @IBOutlet weak var sessionDuration: UILabel?
    @IBOutlet weak var averageLeftForces: UILabel?
    @IBOutlet weak var averageRightForces: UILabel?
    @IBOutlet weak var sendReport: UIButton?
    
    override func awakeFromNib() {
        super.awakeFromNib()
        // Initialization code
    }

    override func setSelected(selected: Bool, animated: Bool) {
        super.setSelected(selected, animated: animated)

        // Configure the view for the selected state
    }

}
