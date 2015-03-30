#include "SMDataSessionManager.h"

SMDataSessionManager sessionManager = SMDataSessionManager();

void setup() {

	Serial.begin(9600);

	while(!Serial); // Leonardo/Micro should wait for serial init

	Serial.println(F("SensMove let's go for folk"));

	sessionManager.InitializeBluetooth();
}

void loop() {
	sessionManager.BleLoopCommunication();
}
