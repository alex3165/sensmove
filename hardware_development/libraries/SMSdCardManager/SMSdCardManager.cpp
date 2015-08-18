/*
*   SMSdCardManager.cpp  - Tool class for browsing a SD card and registrating a session easily
*   Created by Jean-Sébastien Pélerin, April 04, 2015.
*   Copyright SensMove.
*/


// header
#include "SMSdCardManager.h"

/*
*   Constructor
*   @param: name of the session
*/
SMSdCardManager::SMSdCardManager(char* nameSession){
    _myFile = new File();
    _nameSession = nameSession;

}

/*
*   Destructor
*    
*/
SMSdCardManager::~SMSdCardManager(){

}

/*
*   initializeSDCard: tell Arduino to initialize SD card (in the setup)
*    
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
*   createSession: create a session into a file
*
*/
void SMSdCardManager::createSession(){
    _myFile = SD.open(nameSession, FILE_WRITE);
    if (_myFile) {
        Serial.print("Opening file ");
        Serial.println(nameSession);
    } else {
        Serial.print("error opening ");
        Serial.println(nameSession);
    }
    
}


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
*   recordSession: record data in the file we are pointing at
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