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
#define BLEFRAME 17 // Maximum size of a BLE frame
#define STARTSESSION  "start" //Keyword to start the session
#define STOPSESSION "stop" //Keyword to stop the session
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
  // void bleLoopCommunication(String largeData);
  void waitInstruction();
  void sendInstruction(String largeData);

  // void getBTLESerial();
  aci_evt_opcode_t getlastStatus();
  boolean getSessionStarted();

private:

  // private methods
  void sendData(String data);
  void updateStatus();
  String receiveData();


  // Attributes
  Adafruit_BLE_UART* _BTLESerial;
  aci_evt_opcode_t _laststatus;
  boolean _sessionStarted;
  // unsigned int startSessionTime;
  // unsigned int bleCommunicationCounter;
  // void detectValuesLimitations();
};

#endif
