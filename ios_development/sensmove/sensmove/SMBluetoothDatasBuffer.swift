//
//  SMBluetoothDatasBuffer.swift
//  sensmove
//
//  Created by alexandre on 26/08/2015.
//  Copyright (c) 2015 ___alexprod___. All rights reserved.
//

import UIKit

class SMBluetoothDatasBuffer: NSObject {

    var tempStringDatas: NSMutableDictionary!
    
    override init() {
        
        super.init()
        
        self.tempStringDatas = [
            "right": "",
            "left": ""
        ]
    }
    
    func addValue(dataValue: NSData) -> NSData? {
        let currentStringData: NSString = NSString(data: dataValue, encoding: NSUTF8StringEncoding)!
        
        if currentStringData != "" {
            let insoleMarkup: String = currentStringData.substringToIndex(1)
            return self.addValueToInsole(currentStringData.substringFromIndex(1), insole: insoleMarkup == "@" ? "right" : "left")
        } else {
            printLog(currentStringData, "addValue", "No value to add")
            return nil
        }
    }
    
    func addValueToInsole(dataStringValue: String, insole: String) -> NSData? {
        
        if dataStringValue == "" {
            printLog(dataStringValue, "addValue", "No value to add")
            return nil
        }
        
        let isFirstTram: Bool = dataStringValue.substringToIndex(advance(dataStringValue.startIndex, 1)) == "$"
        let isLastTram: Bool = dataStringValue.substringFromIndex(advance(dataStringValue.endIndex, -1)) == "$"
        
        if isFirstTram {
            self.tempStringDatas[insole] = dataStringValue.stringByReplacingOccurrencesOfString("$", withString: "")

            return nil
        }

        let bufferString = self.tempStringDatas.objectForKey(insole) as! String

        if isLastTram {
            
            self.tempStringDatas[insole] = bufferString + dataStringValue.stringByReplacingOccurrencesOfString("$", withString: "")
            
            return self.tempStringDatas.objectForKey(insole)?.dataUsingEncoding(NSUTF8StringEncoding, allowLossyConversion: false)
        }
        
        self.tempStringDatas[insole] = bufferString + dataStringValue

        return nil
        
    }
}
