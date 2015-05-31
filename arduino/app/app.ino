#include <SPI.h>
#include <Adafruit_BLE_UART.h>
#include <ArduinoJson.h>
#include "SMDataManager.h"
#include "SMBLEApplication.h"

SMBLEApplication bleApplication = SMBLEApplication();
int pins[] = {6,0,2,3,5,4,1}; 
SMDataManager dataJson = SMDataManager(pins, 7);
void setup() {

	Serial.begin(9600);
	while(!Serial); // Leonardo/Micro should wait for serial init
        
        bleApplication.InitializeBluetooth();
}

void loop() {
    	//Serial.println(sizeof(hello)/2);
       dataJson.updateData();
       bleApplication.BleLoopCommunication(dataJson.sendJsonData());


      delay(1000);
}
