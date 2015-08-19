//
//  SMSessionController.swift
//  sensmove
//
//  Created by RIEUX Alexandre on 26/05/2015.
//  Copyright (c) 2015 ___alexprod___. All rights reserved.
//

import UIKit

class SMSessionController: UITableViewController, UITableViewDataSource, UITableViewDelegate {
    
    var userSessions: Array<SMSession>!
    
    required init(coder aDecoder: NSCoder) {
        self.userSessions = SMUserService.sharedInstance.currentUser!.sessions
        super.init(coder: aDecoder)
    }

    override func viewDidLoad() {
        super.viewDidLoad()
    }

    override func didReceiveMemoryWarning() {
        super.didReceiveMemoryWarning()
        // Dispose of any resources that can be recreated.
    }

    /// MARK: - Table view data source

    override func numberOfSectionsInTableView(tableView: UITableView) -> Int {
        // #warning Potentially incomplete method implementation.
        // Return the number of sections.
        return 1
    }

    override func tableView(tableView: UITableView, numberOfRowsInSection section: Int) -> Int {
        // #warning Incomplete method implementation.
        // Return the number of rows in the section.
        return self.userSessions.count
    }

    override func tableView(tableView: UITableView, cellForRowAtIndexPath indexPath: NSIndexPath) -> UITableViewCell {
        let currentCell = tableView.dequeueReusableCellWithIdentifier("sessionCell", forIndexPath: indexPath) as! SMSessionViewCell
        currentCell.setSessionViewFromModel(self.userSessions[indexPath.row] as SMSession)
        return currentCell
    }
    
    @IBAction func deleteSessionAction(sender: AnyObject) {

        let buttonPosition: CGPoint = sender.convertPoint(CGPointZero, toView: self.tableView)
        let indexCell: NSIndexPath = self.tableView.indexPathForRowAtPoint(buttonPosition)!
        
        println("Cell index : \(indexCell.item)")
        
        self.userSessions = SMUserService.sharedInstance.deleteSession(indexCell.item)

//        let currentCell = tableView.dequeueReusableCellWithIdentifier("sessionCell", forIndexPath: indexCell) as! SMSessionViewCell
//
//        self.userSessions = self.userSessions.filter({ (session) -> Bool in
//            return session.id != currentCell.sessionIdentifier?.text!
//        })

        self.tableView.reloadData()
        //self.userSessions[indexCell.item].name
//        self.trackSessionService?
    }

    /*
    // Override to support conditional editing of the table view.
    override func tableView(tableView: UITableView, canEditRowAtIndexPath indexPath: NSIndexPath) -> Bool {
        // Return NO if you do not want the specified item to be editable.
        return true
    }
    */

    /*
    // Override to support editing the table view.
    override func tableView(tableView: UITableView, commitEditingStyle editingStyle: UITableViewCellEditingStyle, forRowAtIndexPath indexPath: NSIndexPath) {
        if editingStyle == .Delete {
            // Delete the row from the data source
            tableView.deleteRowsAtIndexPaths([indexPath], withRowAnimation: .Fade)
        } else if editingStyle == .Insert {
            // Create a new instance of the appropriate class, insert it into the array, and add a new row to the table view
        }    
    }
    */

    /*
    // Override to support rearranging the table view.
    override func tableView(tableView: UITableView, moveRowAtIndexPath fromIndexPath: NSIndexPath, toIndexPath: NSIndexPath) {

    }
    */

    /*
    // Override to support conditional rearranging of the table view.
    override func tableView(tableView: UITableView, canMoveRowAtIndexPath indexPath: NSIndexPath) -> Bool {
        // Return NO if you do not want the item to be re-orderable.
        return true
    }
    */

    /*
    // MARK: - Navigation

    // In a storyboard-based application, you will often want to do a little preparation before navigation
    override func prepareForSegue(segue: UIStoryboardSegue, sender: AnyObject?) {
        // Get the new view controller using [segue destinationViewController].
        // Pass the selected object to the new view controller.
    }
    */

}
