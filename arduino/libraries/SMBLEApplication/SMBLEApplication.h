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
  void initializeBluetooth();
  String receiveData();
  void bleLoopCommunication(String largeData);

private:

  // private methods
  void sendData(String data);
  void updateStatus();


  // Attributes
  Adafruit_BLE_UART* _BTLESerial;
  aci_evt_opcode_t _laststatus;
  boolean _sessionStarted;
  // unsigned int startSessionTime;
  // unsigned int bleCommunicationCounter;
  // void detectValuesLimitations();
};

#endif
