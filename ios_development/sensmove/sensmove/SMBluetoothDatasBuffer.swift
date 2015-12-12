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
        var currentStringData: NSString = NSString(data: dataValue, encoding: NSUTF8StringEncoding)!
        
        if currentStringData != "" {
            var insoleMarkup: String = currentStringData.substringToIndex(1)
            // Fix when receiving trame with missing @ markup, need to be fix hardware side
            if insoleMarkup == "$" && currentStringData.length < 2 {
                currentStringData = "@$"
                insoleMarkup = "@"
            }
            return self.addValueToInsole(currentStringData.substringFromIndex(1), insole: insoleMarkup == rightInsoleMarkup ? "right" : "left")
        } else {
            printLog(currentStringData, funcName: "addValue", logString: "No value to add before getting insole markup")
            return nil
        }
    }
    
    func addValueToInsole(dataStringValue: String, insole: String) -> NSData? {
        
        if dataStringValue == "" {
            printLog(dataStringValue, funcName: "addValue", logString: "No value to add after getting insole markup")
            return nil
        }
        
        let isFirstTram: Bool = dataStringValue.substringToIndex(dataStringValue.startIndex.advancedBy(1)) == blockDelimiter
        let isLastTram: Bool = dataStringValue.substringFromIndex(dataStringValue.endIndex.advancedBy(-1)) == blockDelimiter
        
        if isFirstTram {
            self.tempStringDatas[insole] = dataStringValue.stringByReplacingOccurrencesOfString(blockDelimiter, withString: "")

            return nil
        }

        let bufferString = self.tempStringDatas.objectForKey(insole) as! String

        if isLastTram {
            
            self.tempStringDatas[insole] = bufferString + dataStringValue.stringByReplacingOccurrencesOfString(blockDelimiter, withString: "")
            
            return self.tempStringDatas.objectForKey(insole)?.dataUsingEncoding(NSUTF8StringEncoding, allowLossyConversion: false)
        }
        
        self.tempStringDatas[insole] = bufferString + dataStringValue

        return nil
        
    }
}
