#include <SPI.h>
#include <Adafruit_BLE_UART.h>
#include "SMDataManager.h"
#include "SMBLEApplication.h"

#include <Wire.h>
#include <Adafruit_Sensor.h>
#include <Adafruit_LSM303_U.h>

#define ISRIGHT true

//Adafruit_LSM303_Accel_Unified accel = Adafruit_LSM303_Accel_Unified(54321);
//long lastDisplayTime;
//float currentLightLevelX = 0;
//float currentLightLevelY = 0;
//float currentLightLevelZ = 0;

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
	//while(!Serial); // Leonardo/Micro should wait for serial init
//if(!accel.begin()) {
    // There was a problem detecting the LSM303 ... check your connections
  //  Serial.println("Ooops, no LSM303 detected ... Check your wiring!");
    //while(1);
 //}
    // Initialize the SMBLEApplication instance
  bleApplication.initializeBluetooth();
   
}


/*
* Arduino Loop
*
*/
void loop() {
  /*
  * Get accelerometer values
  */
 // sensors_event_t accelEvent;
  //accel.getEvent(&accelEvent);

  /*
  * Map accelerometers values to the led in analog output 13
  */
  //currentLightLevelX = map(accelEvent.acceleration.x, -20, 20, 0, 255);
  // currentLightLevelY = map(accelEvent.acceleration.y, -20, 20, 0, 255);
  //currentLightLevelZ = map(accelEvent.acceleration.z, -20, 20, 0, 255);

  //Serial.print(currentLightLevelX); Serial.print(" \n ");
   // Serial.print(currentLightLevelY); Serial.print(" \n ");
  //Serial.print(currentLightLevelZ); Serial.print(" \n ");  
  
  
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

