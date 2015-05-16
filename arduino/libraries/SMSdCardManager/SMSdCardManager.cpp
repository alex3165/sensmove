//
//  SMSdCardManager.cpp
//  sensmove
//
//  Created by Jean-Sébastien Pélerin on 19/04/2015.
//  Copyright (c) 2015 ___alexprod___. All rights reserved.
//

#include "SMSdCardManager.h"
#include "Arduino.h"
// include the SD library:
#include <SPI.h>
#include <SD.h>

//SMSdCardManager::SMSdCardManager(char* nameSession){
//    _nameSession = nameSession
//}
SMSdCardManager::SMSdCardManager(int port){
    if (!SD.begin(port)) {
        Serial.println("initialization failed!");
        return;
    }
}

void SMSdCardManager::createSession(char* nameSession){
    _myFile = SD.open(nameSession, FILE_WRITE);
    if (_myFile) {
        Serial.print("Opening file ");
        Serial.println(nameSession);
    } else {
        Serial.print("error opening ");
        Serial.println(nameSession);
    }
    
}



void SMSdCardManager::readFolderContent(char* nameFolder){
    
}
void SMSdCardManager::createFolder(char* nameFolder){
    
}
void SMSdCardManager::deleteFolder(char* nameFolder){
    
}
void SMSdCardManager::closeSession(){
    
}
void SMSdCardManager::deleteSession(){
    
}
void SMSdCardManager::recordSession(){
    
}
void SMSdCardManager::readSession(char* reader){
    
}