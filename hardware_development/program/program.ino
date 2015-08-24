#include <SPI.h>
#include <Adafruit_BLE_UART.h>
#include "SMDataManager.h"
#include "SMBLEApplication.h"


// Create an instance of dataManager
int fsrPins[] = {6,0,2,3,5,4,1}; 
int accPins[] = {6,0,2}; 
SMDataManager dataJson = SMDataManager(fsrPins, sizeof(fsrPins)/2, accPins, sizeof(accPins)/2);
// Create an instance of SMBLEApplication
SMBLEApplication bleApplication = SMBLEApplication();

/*
* Arduino Setup
*
*/
void setup() {
	Serial.begin(9600);
	while(!Serial); // Leonardo/Micro should wait for serial init
    // Initialize the SMBLEApplication instance
  bleApplication.initializeBluetooth();
}


/*
* Arduino Loop
*
*/
void loop() {
    
    if(bleApplication.getSessionStarted()){
      // Session has been started
      
      //Send data to device
      dataJson.updateData();
      String jsonData = dataJson.getJsonData();
      bleApplication.sendInstruction(jsonData);
      delay(500);
    } else {
      // Session has not been started

      //Waiting instruction from the external device to manage and send Data
      bleApplication.waitInstruction();
      delay(1000);    
    }
}

