/*
*	SMDataManager.cpp  - Tool class for managing the data from the fsr sensors and the accelerometer as JSON data
*	Created by Jean-Sébastien Pélerin, May 30, 2015.
*	Copyright SensMove.
*/

// header
#include "SMDataManager.h"


//index of the Data object we are sending
int SMDataManager::_index=0;

/*
*	Constructor of the data manager
*	@param: 
*	fsr : array of integer corresponding to the force sensors analog pins
*	dataLength : length of the fsr array
*/
SMDataManager::SMDataManager(int fsr[], int fsrLength, int acc[], int accLength){

	_fsrPins = new int[fsrLength];
	_fsrData = new int[fsrLength];
	_accPins = new int[accLength];
	_accData = new int[accLength];

	for(int i = 0; i < fsrLength; i++){
		_fsrPins[i] = fsr[i];
	}
	for(int j = 0; j < accLength; j++){
		_accPins[j] = acc[j];
	}

	_fsrLength = fsrLength;
	_accLength = accLength;

	_jsonData = String("undefined");
}

/*
* 	Destructor of the Data Manager
*	 
*/
SMDataManager::~SMDataManager(){
	delete [] _fsrPins;
	delete [] _fsrData;
	delete [] _accPins;
	delete [] _accData;
}


/*
* 	updateData: update the data received by the sensors into the data manager
*/
void SMDataManager::updateData(){

	// FSR Sensor data
	for(int i = 0; i < _fsrLength; i++){
		_fsrData[i] = analogRead(_fsrPins[i]);
	}
	// Accelerometer data
	for(int j = 0; j < _accLength; j++){
		_accData[j] = analogRead(_accPins[j]);
	}

	String array1 = createStringArray(_fsrData,_fsrLength);
	String array2 = createStringArray(_accData,_accLength);
	
	// Construction of the json data whe are going to send later by bluetooth LE
	_jsonData = "{\"index\":" +  String(_index) + "\"fsr\":" + array1 + ",\"acc\":" + array2 + "}";   

	_index ++;

}

/*
* 	getJsonData : return the string that represents all the datas organized
*	@return: jsonData data json created by the data manager
*/
String SMDataManager::getJsonData(){
  	
	return _jsonData;
}



/*
*	createStringArray : create an String with an array format from a real int array (format[])
*	@param	Array of integer array - Array to convert to String, int arrayLength - length of the array 
* 	@return String
*/
String SMDataManager::createStringArray(int *array, int arrayLength){
	String data;
	for(int i = 0;i< arrayLength; i++){
		//Serial.println(array[i]);
		if(i == 0){
			data = String(array[i]);
		} else{
			data = data +","+ array[i];
		}
	}
	String stringArray = String("["+data+"]");

	return stringArray;
}


