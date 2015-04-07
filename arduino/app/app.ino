#include "SMDataSessionManager.h"
#include <SPI.h>
#include <Adafruit_BLE_UART.h>

SMDataSessionManager sessionManager = SMDataSessionManager();

void setup() {

	Serial.begin(9600);

	while(!Serial); // Leonardo/Micro should wait for serial init

	Serial.println(F("SensMove let's go for folk"));
}

void loop() {
	sessionManager.BleLoopCommunication();
}
