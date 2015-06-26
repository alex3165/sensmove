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

    dynamic var currentValues: SCNVector3
    var archivedValues: [SCNVector3]
    
    required init(id: Int) {
        self.currentValues = SCNVector3(x: 0, y: 0, z: 0)
        self.archivedValues = []

        super.init(id: id, creation: NSDate())
    }
    
    required init(id: Int, creation: NSDate) {
        fatalError("init(id:creation:) has not been implemented")
    }

    // Set the accelerometer 3d vector to the given values
    func setAccValues(x: Float, y: Float, z: Float) {
        
        self.archivedValues.append(self.currentValues)
        
        self.currentValues.x = x
        self.currentValues.y = y
        self.currentValues.z = z
    }
}