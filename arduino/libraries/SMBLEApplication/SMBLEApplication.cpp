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
	laststatus = ACI_EVT_DISCONNECTED;
	BTLEserial = new Adafruit_BLE_UART(ADAFRUITBLE_REQ, ADAFRUITBLE_RDY, ADAFRUITBLE_RST);
	
}

/*
* 	Destructor of the BLE manager
*/
SMBLEApplication::~SMBLEApplication() {

}

/*
*	Initialization of the bluetooth module
*/
 void SMBLEApplication::InitializeBluetooth() {
 	BTLEserial->setDeviceName("SENS"); /* 7 characters max! */
   	BTLEserial->begin();
  
 }

/*
* 	BLeLoopCommunication : Manage the BLE communication
* 	Should be called in a loop
*	@param: String largeData - string to send 
*/
void SMBLEApplication::BleLoopCommunication(String largeData) {
int jsonDataLength = largeData.length();
Serial.println(jsonDataLength/19);
String data;
for(int i= 0;  i< jsonDataLength+1; i++){
	if(i == jsonDataLength){
		data =	largeData.substring(i*19);
		sendData(data);

	} else {
		data = largeData.substring(i*19,(i+1)*19);
		sendData(data);

	}
}

}

/*
* 	SendData : Method to send a String 
*	@param:  String data - string to send, the string length must be inferior to 20 byte arrays (BLE limitations)
*/
void SMBLEApplication::sendData(String data){

	// Waiting for call back to send next split data 

 	BTLEserial->pollACI(); // Tell the nRF8001 to do whatever it should be working on.
	aci_evt_opcode_t status = BTLEserial->getState(); // Ask what is our current status

 	if (status != laststatus) {  // If the status changed....

		if (status == ACI_EVT_DEVICE_STARTED) {
		    Serial.println(F("* Advertising started"));
		}
		if (status == ACI_EVT_CONNECTED) {
		    Serial.println(F("* Connected!"));
		}
		if (status == ACI_EVT_DISCONNECTED) {
		    Serial.println(F("* Disconnected or advertising timed out"));
		}

		laststatus = status; // OK set the last status change to this one
	}

	if (status == ACI_EVT_CONNECTED) { // If phone device is connected

		// To receiving datas
		if (BTLEserial->available()) {
			Serial.print("* ");
			Serial.print(BTLEserial->available());
			Serial.println(F(" bytes available from BTLE"));

		}

		// Send data by bluetooth
		BTLEserial->print(data);
		
	}
}
