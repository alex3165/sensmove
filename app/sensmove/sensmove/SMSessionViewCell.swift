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
    
    override func awakeFromNib() {
        super.awakeFromNib()
    }

    override func setSelected(selected: Bool, animated: Bool) {
        super.setSelected(selected, animated: animated)
    }

    func setSessionViewFromModel(session: SMSession) {
        
        self.designComponents()
        
        // Set values for components
        self.sessionTitle?.text = session.name as String
        self.averageLeftForces?.text = session.averageLeftForce?.stringValue
        self.averageRightForces?.text = session.averageRightForce?.stringValue
        self.sessionDuration?.text = session.duration!.stringValue
    }
    
    // Set colors for differents component of session view
    func designComponents() {
        self.headerView?.backgroundColor = SMColor.ligthGrey()
        
        self.sessionDuration?.textColor = SMColor.red()
        self.averageRightForces?.textColor = SMColor.red()
        self.averageLeftForces?.textColor = SMColor.red()

        self.sendReport?.setTitleColor(SMColor.middleGrey(), forState: UIControlState.Normal)
    }
}
