/*
*   SMSdCardManager.cpp  - Tool class for browsing a SD card and registrating a session easily
*   Created by Jean-Sébastien Pélerin, April 04, 2015.
*   Copyright SensMove.
*/


// header
#include "SMSdCardManager.h"

/*
* 
*   @param: 
*/
SMSdCardManager::SMSdCardManager(char* nameSession){
    _myFile = new File();
    _nameSession = nameSession;

}

/*
* 
*   @param: 
*/
SMSdCardManager::~SMSdCardManager(){

}

/*
* 
*   @param: 
*/
void SMSdCardManager::initializeSDCard(){
    if (!SD.begin(port)) {
        Serial.println("initialization failed!");
        return;
    } else {
        Serial.println("initialization is ok")
    }
}

/*
* 
*   @param: 
*/
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


// /*
// *   readFolderContent
// *   @param: 
// */
// void SMSdCardManager::readFolderContent(char* nameFolder){
    
// }

// /*
// *   createFolder
// *   @param: 
// */
// void SMSdCardManager::createFolder(char* nameFolder){
    
// }

// /*
// *   deleteFolder
// *   @param: 
// */
// void SMSdCardManager::deleteFolder(char* nameFolder){
    
// }

/*
*   closeSession: close the session in which we are pointing
*   
*/
void SMSdCardManager::closeSession(){
    
}

/*
*   deleteSession: delete the session in which we are pointing
*    
*/
void SMSdCardManager::deleteSession(){
    
}

/*
*   recordSession
*
*/
void SMSdCardManager::recordSession(){
    
}

/*
*   readSession
*   @param: 
*/
void SMSdCardManager::readSession(char* reader){
    
}