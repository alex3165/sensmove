/*
*   SMBLEApplication.cpp - BLE module to communicate with a device
*   Created by Alexandre Rieux, March 30, 2015.
*   Copyright SensMove.
*/

// header
#include "SMBLEApplication.h"

/*
*	Constructor of the BLE manager
*/
SMBLEApplication::SMBLEApplication(){

	// sessionId = 0;
	// startSessionTime = 0;
	// bleCommunicationCounter = 0;
	_laststatus = ACI_EVT_DISCONNECTED;
	_BTLESerial = new Adafruit_BLE_UART(ADAFRUITBLE_REQ, ADAFRUITBLE_RDY, ADAFRUITBLE_RST);
	_sessionStarted = false;
}

/*
* 	Destructor of the BLE manager
*/
SMBLEApplication::~SMBLEApplication() {

	free(_BTLESerial);

}

/*
*	initializeBluetooth: Initialization of the bluetooth module
*/
 void SMBLEApplication::initializeBluetooth() {
 	_BTLESerial->setDeviceName("SL18902"); /* 7 characters max! */
   	_BTLESerial->begin();
  
 }

/*
* 	bLeLoopCommunication : Manage the BLE communication
* 	Should be called in a loop
*	@param: String largeData - string to send 
*/
void SMBLEApplication::bleLoopCommunication(String largeData) {

	String dataExtDevice;
	String dataCurDevice;
	if(!_sessionStarted){
		//Session did not started, wait for start request

		dataExtDevice = receiveData();
		_sessionStarted = dataExtDevice.equals(String("start"));
		// Serial.println(_sessionStarted);

	} else {
		//Send Sensors Data to the external device

		int jsonDataLength = largeData.length()/17;

		// if(jsonDataLength>0){
			for(int i= 0;  i< jsonDataLength+1; i++){
				if(i == jsonDataLength){
					dataCurDevice =	"$" +largeData.substring(i*17) +"$";
					sendData(dataCurDevice);
					Serial.println(dataCurDevice);
				} else {
					dataCurDevice = "$" + largeData.substring(i*17,(i+1)*17) + "$";
					sendData(dataCurDevice);
					Serial.println(dataCurDevice);

				}

			}

		// }
		
		//Check if their is a request from the external device
		dataExtDevice = receiveData();
		_sessionStarted = !dataExtDevice.equals(String("stop"));

	}
		



}

/*
*	receiveData: function to wait for data from an external device
*	@return data received from the external device
*/
String SMBLEApplication::receiveData(){
	String stringBle = String("");
 	_BTLESerial->pollACI(); // Tell the nRF8001 to do whatever it should be working on.
	updateStatus();

	if (_laststatus == ACI_EVT_CONNECTED) { // If phone device is connected

			// To receiving datas
			while (_BTLESerial->available()) {
		       char data = _BTLESerial->read();
		       stringBle = stringBle + String(data);
			}
			if(stringBle.length()>0){
				Serial.println(stringBle);
			}
		
	}
	return stringBle;
}

/*
* updateStatus : get
*
*/
void SMBLEApplication::updateStatus(){

	aci_evt_opcode_t status = _BTLESerial->getState(); // Ask what is our current status

	
	if (status != _laststatus) {  // If the status changed....

	if (status == ACI_EVT_DEVICE_STARTED) {
	    Serial.println(F("* Advertising started"));
	}
	if (status == ACI_EVT_CONNECTED) {
	    Serial.println(F("* Connected!"));
	}
	if (status == ACI_EVT_DISCONNECTED) {
	    Serial.println(F("* Disconnected or advertising timed out"));
	}

	_laststatus = status; // OK set the last status change to this one
	}

	_laststatus = status;

}

/*
* 	SendData : Method to send a String 
*	@param:  String data - string to send, the string length must be inferior to 20 byte arrays (BLE limitations)
*/
void SMBLEApplication::sendData(String data){

	// Waiting for call back to send next split data 

 	_BTLESerial->pollACI(); // Tell the nRF8001 to do whatever it should be working on.

	updateStatus();

	if (_laststatus == ACI_EVT_CONNECTED) { // If phone device is connected

	// Send data by bluetooth
	_BTLESerial->print(data);
				
 }
	
	
}
