/*
  SMDataManager.h - I/O library for managing sensors value and sending it over bluetooth.
  Created by Alexandre Rieux, March 30, 2015.
  Copyright SensMove.
*/

#ifndef SMDataSessionManager
#define SMDataSessionManager

/**
*
*	Include libraries
*
*/
#include <SPI.h>
#include <Adafruit_BLE_UART.h>

/**
*
*	Define contants
*
*/
#define ADAFRUITBLE_REQ 10
#define ADAFRUITBLE_RDY 2
#define ADAFRUITBLE_RST 9

class SMDataSessionManager {

  public:

    SMDataSessionManager();
    ~SMDataSessionManager();

    int sessionId;

  	time_t currentTime; // Current time set by the first bluetooth communication
  	time_t startSessionTime;
  	time_t stopSessionTime;

    // bool SendDatasOverBluetooth();
    void BleLoopCommunication();

  private:
    
  	int bleCommunicationCounter;

	  void InitializeBluetooth();
	  void RetrievingBleDatas();

    // void detectValuesLimitations();
    void SetCurrentTime();
};

#endif
