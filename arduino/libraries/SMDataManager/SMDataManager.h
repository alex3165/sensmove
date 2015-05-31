//
//  SMSdCardManager.h  - Tool class for browsing a SD card and registrating a session easily
//  sensmove
//
//  Created by Jean-Sébastien Pélerin on 30/05/2015.
//  Copyright (c) 2015 ___alexprod___. All rights reserved.
//
#include "Arduino.h"
#include "ArduinoJson.h"

#ifndef _SMDataManager_h
#define _SMDataManager_h

class SMDataManager {
	public:
		SMDataManager(int* fsr, int dataLength);
		~SMDataManager();
		void updateData();
		char* sendJsonData();

	private:
		// JsonObject& jsonData;
		// void 
		int * fsrPins;
		int * fsrData;
		int tabLength;
		char * jsonChar;

};

#endif