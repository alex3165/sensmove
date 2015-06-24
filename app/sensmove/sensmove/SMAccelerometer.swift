/**
*  SMSensor
*  SensMove
*
*  @author Alexandre Rieux and Jean-Sébastien Pélerin
*  @date 13/03/2015
*  @copyright (c) 2014 SensMove. All rights reserved.
*/


import Foundation
import SceneKit

class SMAccelerometer: SMSensor {

    var values: SCNVector3?
    
    required init(id: Int, pos: JSON) {

        super.init(id: id, creation: NSDate())

    }
    
    required init(id: Int, creation: NSDate) {
        fatalError("init(id:creation:) has not been implemented")
    }

    // Set the accelerometer 3d vector to the given values
    func setAccValues(x: Float, y: Float, z: Float) {
        self.values?.x = x
        self.values?.y = y
        self.values?.z = z
    }
}