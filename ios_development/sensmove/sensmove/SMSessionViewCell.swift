//
//  SMSessionViewCell.swift
//  sensmove
//
//  Created by RIEUX Alexandre on 30/05/2015.
//  Copyright (c) 2015 ___alexprod___. All rights reserved.
//

import UIKit

class SMSessionViewCell: UITableViewCell {

//    @IBOutlet weak var sessionIdentifier: UILabel?
    @IBOutlet weak var sessionDuration: UILabel?
    @IBOutlet weak var averageLeftForces: UILabel?
    @IBOutlet weak var averageRightForces: UILabel?
    @IBOutlet weak var sendReport: UIButton?
    @IBOutlet weak var sessionTitle: UILabel?
    @IBOutlet weak var headerView: UIView?
    @IBOutlet weak var deleteSessionButton: UIButton?
    @IBOutlet weak var sessionIdentifier: UILabel?
    
    override func awakeFromNib() {
        super.awakeFromNib()
    }

    override func setSelected(selected: Bool, animated: Bool) {
        super.setSelected(selected, animated: animated)
    }

    func setSessionViewFromModel(session: SMSession) {

        self.designComponents()

        // Set values for components
        self.sessionIdentifier?.text = session.id as String
        self.sessionTitle?.text = session.name as String

        if let leftForceStringValue = session.averageLeftForce?.stringValue {
            self.averageLeftForces?.text = "\(leftForceStringValue) psi"
        }
        
        if let rightForceStringValue = session.averageRightForce?.stringValue {
            self.averageRightForces?.text = "\(rightForceStringValue) psi"
        }

        self.sessionDuration?.text = "\(self.stringFromTimeInterval(session.duration!))"
        
    }

    func stringFromTimeInterval(interval: NSTimeInterval) -> String {
        let interval = Int(interval)
        let seconds = interval % 60
        let minutes = (interval / 60) % 60
        let hours = (interval / 3600)
        return String(format: "%02d:%02d:%02d", hours, minutes, seconds)
    }

    // Set colors for differents component of session view
    func designComponents() {
        self.headerView?.backgroundColor = SMColor.ligthGrey()
        
        self.sessionDuration?.textColor = SMColor.red()
        self.averageRightForces?.textColor = SMColor.red()
        self.averageLeftForces?.textColor = SMColor.red()

        self.sendReport?.setTitleColor(SMColor.whiteColor(), forState: UIControlState.Normal)
        self.sendReport?.backgroundColor = SMColor.green()
        self.sendReport?.layer.cornerRadius = 4
        
        self.deleteSessionButton?.setTitleColor(SMColor.whiteColor(), forState: UIControlState.Normal)
        self.deleteSessionButton?.backgroundColor = SMColor.red()
        self.deleteSessionButton?.layer.cornerRadius = 4
        
        self.sessionIdentifier?.textColor = SMColor.lightGrayColor()
    }
}
