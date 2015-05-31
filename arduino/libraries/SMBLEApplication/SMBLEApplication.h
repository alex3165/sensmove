/*
  SMDataManager.h - I/O library for managing sensors value and sending it over bluetooth.
  Created by Alexandre Rieux, March 30, 2015.
  Copyright SensMove.
*/

#include "Arduino.h"
#include "Adafruit_BLE_UART.h"

#ifndef _SMBLEAPPLICATION_H_
#define _SMBLEAPPLICATION_H_


/**
*
*	Define constants
*
*/
#define ADAFRUITBLE_REQ 10
#define ADAFRUITBLE_RDY 2
#define ADAFRUITBLE_RST 9

class SMBLEApplication {

  public:

    SMBLEApplication();
    ~SMBLEApplication();

    void InitializeBluetooth();

    // bool SendDatasOverBluetooth();
    void BleLoopCommunication(char* jsonData);

   private:
  //   unsigned int sessionId;

  //   unsigned int currentTime; // Current time set by the first bluetooth communication
  //   unsigned int startSessionTime;
  // 	unsigned int bleCommunicationCounter;
    Adafruit_BLE_UART* BTLEserial;
    aci_evt_opcode_t laststatus;
    // void detectValuesLimitations();
    // void SetCurrentTime();
};

#endif
