//
//  SMSdCardManager.h  - Tool class for browsing a SD card and registrating a session easily
//  sensmove
//
//  Created by Jean-Sébastien Pélerin on 19/04/2015.
//  Copyright (c) 2015 ___alexprod___. All rights reserved.
//

#ifndef _SMSdCardManager_h
#define _SMSdCardManager_h

class SMSdCardManager{
    
public:
    void readFolderContent(char* nameFolder);
    void createFolder(char* nameFolder);
    void deleteFolder(char* nameFolder);
    void createSession(char* nameSession);
    void closeSession();
    void deleteSession();
    void recordSession();
    void readSession(char* reader);
private:
    char* _nameSession;
    File _myFile
    
};

//initialize SD Card

//read SD Card content

//create folder

//delete folder

//Create new Session record

//Delete Session/File

//Record session

//Read Session



#endif
