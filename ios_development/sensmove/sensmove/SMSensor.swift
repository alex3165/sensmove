/**
*  SMSensor
*  SensMove
*
*  @author Alexandre Rieux and Jean-Sébastien Pélerin
*  @date 10/11/2014
*  @copyright (c) 2014 SensMove. All rights reserved.
*/

import Foundation


/**
*
*   Sensor primary class, used by SMForce and SMAccelerometer classes
*
*/

class SMSensor: NSObject {
    
    /// Sensor Id
    var id: Int

    /// Sensor creational date
    let creationalDate: NSDate

    required init(id:Int, creation:NSDate) {
        self.id = id
        self.creationalDate = creation
        super.init()
    }

}