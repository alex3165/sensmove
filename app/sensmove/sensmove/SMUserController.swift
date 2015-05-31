//
//  SMUserController.swift
//  sensmove
//
//  Created by RIEUX Alexandre on 20/04/2015.
//  Copyright (c) 2015 ___alexprod___. All rights reserved.
//

import UIKit

class SMUserController: UIViewController {
    
    @IBOutlet weak var topBanner: UIView?
    @IBOutlet weak var profilePicture: UIImageView?
    
    @IBOutlet weak var firstName: UILabel?
    @IBOutlet weak var lastName: UILabel?
    @IBOutlet weak var email: UILabel?
    @IBOutlet weak var weight: UILabel?
    @IBOutlet weak var height: UILabel?
    @IBOutlet weak var doctor: UILabel?
    @IBOutlet weak var balance: UILabel?
    @IBOutlet weak var averageForceRight: UILabel?
    @IBOutlet weak var averageForceLeft: UILabel?
    @IBOutlet weak var diseaseDescription: UITextView?

    @IBOutlet weak var delimiter: UIView?
    
    @IBOutlet weak var editButton: UIButton?
    
    var userService: SMUserService
    
    
    required init(coder aDecoder: NSCoder) {
        self.userService = SMUserService.sharedInstance
        super.init(coder: aDecoder)
    }
    
    override func viewDidLoad() {
        super.viewDidLoad()
        
        self.setupUserProfileView()
    }

    override func didReceiveMemoryWarning() {
        super.didReceiveMemoryWarning()
        // Dispose of any resources that can be recreated.
    }
    
    func setupUserProfileView() {
        var colorManager: SMColor = SMColor()
        
        self.profilePicture!.layer.cornerRadius = self.profilePicture!.frame.size.height/2;
        self.profilePicture!.layer.masksToBounds = true;
        self.profilePicture!.layer.borderColor = colorManager.lightOrange().CGColor
        self.profilePicture!.layer.borderWidth = 5.0;
        self.profilePicture!.image =  UIImage(named: self.userService.currentUser?.picturePath as! String)

        let locations: [Float] = [0.2, 1]
        var smGradient: SMGradient = SMGradient(locations: locations)
        smGradient.setFrameFromRect(self.topBanner!.bounds)

        self.topBanner!.layer.insertSublayer(smGradient, atIndex: 0)
        
        
        self.firstName?.text = self.userService.currentUser?.firstName as? String
        self.lastName?.text = self.userService.currentUser?.lastName as? String
        self.email?.text = self.userService.currentUser?.email as? String
        self.weight?.text = String(stringInterpolationSegment: self.userService.currentUser?.weight as! NSInteger)
        self.height?.text = String(stringInterpolationSegment: self.userService.currentUser?.height as! NSInteger)
        self.doctor?.text = self.userService.currentUser?.doctor as? String
        self.balance?.text = self.userService.currentUser?.balance as? String
        self.averageForceLeft?.text = String(stringInterpolationSegment: self.userService.currentUser?.averageForceLeft as! NSInteger)
        self.averageForceRight?.text = String(stringInterpolationSegment: self.userService.currentUser?.averageForceRight as! NSInteger)
        self.diseaseDescription?.text = self.userService.currentUser?.diseaseDescription as? String
    }
}