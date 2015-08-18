/*
*   SMDataManager.h - I/O library for managing sensors value and sending it over bluetooth.
*   Created by Alexandre Rieux, March 30, 2015.
*   Copyright SensMove.
*/

/*
*  Dependencies
*/
#include "Arduino.h"

/*
*  Define file
*/
#ifndef _SMDataManager_h
#define _SMDataManager_h

/*
*  Class
*/
class SMDataManager {

	public:
		// constructors
		SMDataManager(int* fsr, int fsrLength, int *acc, int accLength);
		~SMDataManager();

		// public methods
		void updateData();

		//getter
		String getJsonData();


	private:
		// private methods
		String createStringArray(int *array, int arrayLength);

		// attributes
		// int _jsonDataLength;
		int *_fsrPins;//
		int *_accPins;//

		int *_fsrData;//
		int *_accData;//

		int _fsrLength;//
		int _accLength;//
		String _jsonData;//
		static int _index;

};

#endif