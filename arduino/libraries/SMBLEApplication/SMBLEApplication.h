/*
*   SMBLEApplication.h - BLE module to communicate with a device
*   Created by Alexandre Rieux, March 30, 2015.
*   Copyright SensMove.
*/

/*
*  Dependencies
*/
#include "Arduino.h"
#include "Adafruit_BLE_UART.h"

/*
*   Define file
*/
#ifndef _SMBLEAPPLICATION_H_
#define _SMBLEAPPLICATION_H_

/*
*  Constants
*/
#define BLEFRAME 20 // Maximum size of a BLE frame
#define ADAFRUITBLE_REQ 10
#define ADAFRUITBLE_RDY 2
#define ADAFRUITBLE_RST 9

/*
*  Class
*/
class SMBLEApplication {

public:

  // constructors
  SMBLEApplication();
  ~SMBLEApplication();

  // public methods
  void InitializeBluetooth();
  void BleLoopCommunication(String largeData);

private:

  // private methods
  void sendData(String data);


  // Attributes
  Adafruit_BLE_UART* BTLEserial;
  aci_evt_opcode_t laststatus;
  // unsigned int sessionId;
  // unsigned int currentTime; // Current time set by the first bluetooth communication
  // unsigned int startSessionTime;
  // unsigned int bleCommunicationCounter;
  // void detectValuesLimitations();
  // void SetCurrentTime();
};

#endif
