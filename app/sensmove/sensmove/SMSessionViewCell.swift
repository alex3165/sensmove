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
    @IBOutlet weak var sessionTitle: UILabel?
    @IBOutlet weak var headerView: UIView?

    var colorManager: SMColor?
    
    override func awakeFromNib() {
        super.awakeFromNib()

        self.colorManager = SMColor()
    }

    override func setSelected(selected: Bool, animated: Bool) {
        super.setSelected(selected, animated: animated)

        // Configure the view for the selected state
    }

    func setSessionViewFromModel(session: SMSession) {
        
        self.designComponents()
        
        self.sessionTitle?.text = session.name as String
        self.averageLeftForces?.text = session.averageLeftForce?.stringValue
        self.averageRightForces?.text = session.averageRightForce?.stringValue
        self.sessionDuration?.text = session.duration!.stringValue
    }
    
    func designComponents() {
        self.headerView?.backgroundColor = self.colorManager?.ligthGrey()
        
        self.sessionDuration?.textColor = self.colorManager?.red()
        self.averageRightForces?.textColor = self.colorManager?.red()
        self.averageLeftForces?.textColor = self.colorManager?.red()

        self.sendReport?.setTitleColor(self.colorManager?.middleGrey(), forState: UIControlState.Normal)
    }
}
