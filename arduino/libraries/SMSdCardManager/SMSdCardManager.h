/*
*   SMSdCardManager.h  - Tool class for browsing a SD card and registrating a session easily
*   Created by Jean-Sébastien Pélerin, April 04, 2015.
*   Copyright SensMove.
*/

/*
*  Dependencies
*/
#include "Arduino.h"
#include <SPI.h>
#include <SD.h>

/*
*   Define file
*/
#ifndef _SMSdCardManager_h
#define _SMSdCardManager_h

/*
*  Class
*/
class SMSdCardManager{

public:
    // constructors
    SMSdCardManager(File myFile, char* nameSession)
    ~SMSdCardManager()

    // public methods
    void initializeSDCard();
    void readFolderContent(char* nameFolder);
    void createFolder(char* nameFolder);
    void deleteFolder(char* nameFolder);
    void recordSession(char* nameSession);
    void closeSession();
    void deleteSession();
    void recordSession();
    void readFileContent(char* reader);
private:
    // attributes
    char* _nameSession;
    File _myFile

    
};




#endif
