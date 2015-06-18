#include <SPI.h>
#include <Adafruit_BLE_UART.h>
#include <ArduinoJson.h>
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
               //dataJson.updateData();

     bleApplication.InitializeBluetooth();
}

void loop() {
//Serial.println("hello");
       dataJson.updateData();

      String jsonData = dataJson.getJsonData();
       //int jsonDataLength = dataJson.getJsonDataLength();
     //Serial.println(jsonData);
     //Serial.println(jsonDataLength);
       bleApplication.BleLoopCommunication(jsonData);

             // Serial.println(jsonData);
             // Serial.println(jsonDataLength);
        delay(1000);

}
