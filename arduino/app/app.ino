#include <SPI.h>
#include <Adafruit_BLE_UART.h>
#include "SMDataManager.h"
#include "SMBLEApplication.h"


SMBLEApplication bleApplication = SMBLEApplication();
int fsrPins[] = {6,0,2,3,5,4,1}; 
int accPins[] = {6,0,2}; 

  //String thisString = String(13);
//String secondString = String(thisString + "coco");

SMDataManager dataJson = SMDataManager(fsrPins, sizeof(fsrPins)/2, accPins, sizeof(accPins)/2);
void setup() {
	Serial.begin(9600);
	while(!Serial); // Leonardo/Micro should wait for serial init

     bleApplication.initializeBluetooth();
}

void loop() {
    
    if(bleApplication.getSessionStarted()){
      //External device is connected and has sent the start request
      
      //Send data to device
      dataJson.updateData();
      String jsonData = dataJson.getJsonData();
      bleApplication.sendInstruction(jsonData);
      delay(1000);

      
    } else {
      //Waiting instruction from the external device to manage and send Data
       bleApplication.waitInstruction();
       Serial.println("Waiting...");
      delay(5000);

    
    }

}
