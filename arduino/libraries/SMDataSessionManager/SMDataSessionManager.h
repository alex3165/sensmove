/*
  SMDataManager.h - I/O library for managing sensors value and sending it over bluetooth.
  Created by Alexandre Rieux, March 30, 2015.
  Copyright SensMove.
*/

#ifndef _SMDATASESSIONMANAGER_H_
#define _SMDATASESSIONMANAGER_H_

/**
*
*	Define constants
*
*/
#define ADAFRUITBLE_REQ 10
#define ADAFRUITBLE_RDY 2
#define ADAFRUITBLE_RST 9

class SMDataSessionManager {

  public:

    SMDataSessionManager();
    ~SMDataSessionManager();

    // bool SendDatasOverBluetooth();
    void BleLoopCommunication(unsigned int fsrReading);
    void RetrievingBleDatas();

  private:
    unsigned int sessionId;

    unsigned int currentTime; // Current time set by the first bluetooth communication
    unsigned int startSessionTime;
  	unsigned int bleCommunicationCounter;
    char lastData[]; 

    // void detectValuesLimitations();
    void SetCurrentTime();
    void InitializeBluetooth();
};

#endif
