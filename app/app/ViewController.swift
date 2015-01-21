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
    var arraySensors:[SMSensor]?
    @IBOutlet weak var sceneView: SCNView!
    
    override func viewDidLoad() {
        super.viewDidLoad()
//        self.arraySensors = NSMutableArray()
        let scene:SCNScene = SCNScene()
        
        for index in 1...self.nbSensors {
            var indexFloat:Float = Float(index)
            var sens:SMSensor = SMSensor(id: index, pos: SCNVector3Make(0, 0, indexFloat))
//            println(sens.id)
            scene.rootNode.addChildNode(sens.getNode())
            self.arraySensors?.append(sens)
        }
//        scene.background = SCNMaterialProperty
        // Initialization of the scene
        self.sceneView.scene = scene;
        
        // Allow touch gesture control the camera
        self.sceneView.allowsCameraControl = true
        
    }

    override func didReceiveMemoryWarning() {
        super.didReceiveMemoryWarning()
    }
    
    override func viewWillTransitionToSize(size: CGSize, withTransitionCoordinator coordinator: UIViewControllerTransitionCoordinator) {
        super.viewWillTransitionToSize(size, withTransitionCoordinator: coordinator)
        sceneView.stop(nil)
        sceneView.play(nil)
    }

}

