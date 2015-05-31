#include "SMBLEApplication.h"

/**
*	Constructor for the BLE manager
*/
SMBLEApplication::SMBLEApplication(){

	// sessionId = 0;
	// startSessionTime = 0;
	// bleCommunicationCounter = 0;
	// BTLEserial = malloc(sizeof(Adafruit_BLE_UART));
	laststatus = ACI_EVT_DISCONNECTED;
	BTLEserial = new Adafruit_BLE_UART(ADAFRUITBLE_REQ, ADAFRUITBLE_RDY, ADAFRUITBLE_RST);
	// laststatus = ACI_EVT_DISCONNECTED;
	// InitializeBluetooth();
	
}

SMBLEApplication::~SMBLEApplication() {

}
/**
*	Initialization of the bluetooth module
*/
 void SMBLEApplication::InitializeBluetooth() {
 	BTLEserial->setDeviceName("SENS"); /* 7 characters max! */
   	BTLEserial->begin();
  
 }

void SMBLEApplication::BleLoopCommunication(char* jsonData) {

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

	/*
	*   To receiving datas
	*/
	if (BTLEserial->available()) {
		Serial.print("* ");
		Serial.print(BTLEserial->available());
		Serial.println(F(" bytes available from BTLE"));

	} 
	/*
	* 	Send data by bluetooth
	*/
	BTLEserial->print(jsonData);
	}
}

// void SMBLEApplication::SetCurrentTime() {

// }
