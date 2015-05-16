#include "SMDataSessionManager.h"
#include <SPI.h>
#include <Adafruit_BLE_UART.h>
#include <stdint.h>

SMDataSessionManager sessionManager = SMDataSessionManager();
int intTest = 1;
void setup() {

	Serial.begin(9600);

	while(!Serial); // Leonardo/Micro should wait for serial init

	Serial.println(F("SensMove let's go for folk"));
}

void loop() {
	sessionManager.BleLoopCommunication(intTest);
}
