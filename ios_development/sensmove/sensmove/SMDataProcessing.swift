//
//  SMDataProcessing.swift
//  sensmove
//
//  Created by alexandre on 30/08/2015.
//  Copyright (c) 2015 ___alexprod___. All rights reserved.
//

import UIKit

class SMDataProcessing: NSObject {
    override init() {
        super.init()
    }
    
    func removeNoise(datas: JSON) -> JSON {
        
        var activeValuesIndexes: Array = [Int]()
        var possibleNoiseIndexes: Array = [Int]()
        
        var trueArr: Array = datas["fsr"].arrayValue.map { (value:JSON) -> Float in
            return value.floatValue
        }

        for(var i = 0; i < trueArr.count; i++) {
            if trueArr[i] > 10 {
                activeValuesIndexes.append(i)
            }

            if trueArr[i] < 10 && trueArr[i] > 0 {
                possibleNoiseIndexes.append(i)
            }
        }

        if possibleNoiseIndexes.count > 0 && activeValuesIndexes.count > 0 {
            for noiseIndex: Int in possibleNoiseIndexes {
                trueArr[noiseIndex] = Float(0)
            }

            return JSON(["fsr": trueArr])
        }
        
        return datas
    }
}
