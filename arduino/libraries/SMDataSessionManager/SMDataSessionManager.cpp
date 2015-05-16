#include "Arduino.h"
#include "SMDataSessionManager.h"
#include "Adafruit_BLE_UART.h"
/**
*
*	Bluetooth necessary objects
*
*/
Adafruit_BLE_UART BTLEserial = Adafruit_BLE_UART(ADAFRUITBLE_REQ, ADAFRUITBLE_RDY, ADAFRUITBLE_RST);
aci_evt_opcode_t laststatus = ACI_EVT_DISCONNECTED;


/**
*	Constructor for the session manager
*/
SMDataSessionManager::SMDataSessionManager()
	: sessionId(0), currentTime(0), startSessionTime(0), bleCommunicationCounter(0), lastData() {
	InitializeBluetooth();
}

SMDataSessionManager::~SMDataSessionManager() {
}

void SMDataSessionManager::InitializeBluetooth() {
  BTLEserial.setDeviceName("SENSMOVE"); /* 7 characters max! */
  BTLEserial.begin();
}

void SMDataSessionManager::BleLoopCommunication(unsigned int fsrReading) {

	BTLEserial.pollACI(); // Tell the nRF8001 to do whatever it should be working on.

	aci_evt_opcode_t status = BTLEserial.getState(); // Ask what is our current status

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
	if (BTLEserial.available()) {
		Serial.print("* ");
		Serial.print(BTLEserial.available());
		Serial.println(F(" bytes available from BTLE"));

		RetrievingBleDatas();
	} 
	/*
	* 	Send data by bluetooth
	*/
	BTLEserial.print(fsrReading);
	}
}

void SMDataSessionManager::RetrievingBleDatas() {

	if (bleCommunicationCounter < 1) { // first ble communication
		// while (BTLEserial.available()) {
		//   lastData += BTLEserial.read();
		// }
	}

	bleCommunicationCounter++;
}

void SMDataSessionManager::SetCurrentTime() {

}
