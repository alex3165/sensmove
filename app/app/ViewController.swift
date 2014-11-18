//
//  ViewController.swift
//  app
//
//  Created by RIEUX Alexandre on 17/11/2014.
//  Copyright (c) 2014 RIEUX Alexandre. All rights reserved.
//

import UIKit
import SceneKit


class ViewController: UIViewController {
    
    let nbSensors:Int=7
    var arraySensors:NSMutableArray?
    
    
    override func viewDidLoad() {
        super.viewDidLoad()
        
        
        for index in 1...self.nbSensors {
            var indexFloat:Float = Float(index)
            var vector:Vector3D = Vector3D (x: 2, y: 3, z : 3)
            var sens:SMSensor=SMSensor(id: index, pos: vector)
            self.arraySensors?.addObject(sens)
        }
        // Do any additional setup after loading the view, typically from a nib.
    }

    override func didReceiveMemoryWarning() {
        super.didReceiveMemoryWarning()
        // Dispose of any resources that can be recreated.
    }

    
    func sceneSetup() {
        let scene:SCNScene = SCNScene()
        
    }
}

