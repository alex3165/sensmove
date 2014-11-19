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
    @IBOutlet weak var sceneView: SCNView!
    
    override func viewDidLoad() {
        super.viewDidLoad()
        
        for index in 1...self.nbSensors {
            var indexFloat:Float = Float(index)
            self.arraySensors?.addObject(SMSensor(id: index, pos: SCNVector3Make(10, 10, 10)))
        }
        
        // Initialization of the scene
        sceneSetup();
        
        // Allow touch gesture control the camera
        sceneView.allowsCameraControl = true
        
    }

    override func didReceiveMemoryWarning() {
        super.didReceiveMemoryWarning()
    }

    
    func sceneSetup() {
        let scene:SCNScene = SCNScene()
        
        
        
        sceneView.scene = scene;
    }
}

