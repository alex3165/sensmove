#include "SMDataManager.h"

/**
*	Constructor for the data manager
*/

SMDataManager::SMDataManager(int *fsr, int dataLength){
	fsrPins = new int[dataLength];
	fsrData = new int[dataLength];
	tabLength = dataLength;
	int i;
	for(i = 0; i < tabLength; i++){
		fsrPins[i] = fsr[i];
	}
	// jsonData = jsonBuffer.parseObject("{\"hello\":\"yo\"}");
}
SMDataManager::~SMDataManager(){

}

void SMDataManager::updateData(){
	// int i;
	// for(i = 0; i< tabLength; i++){
	// 	fsrData[i] = analogRead(fsrPins[i]);
	// // 	Serial.print(" Sensor ");
	// // 	Serial.print(i);
	// // 	Serial.print(": ");
	// // 	Serial.print(fsrData[i]);
	// }
	// 	Serial.println();

	StaticJsonBuffer<400> jsonBuffer;
  	JsonObject& root = jsonBuffer.createObject();
  	// JsonArray& data = root.createNestedArray("data");
  	// JsonObject& block = jsonBuffer.createObject();
  	// root["idDevice"] = "SM1234242";
  	// root["timeStartSession"] = 201404;
  	JsonArray& fsr = root.createNestedArray("fsr");
  	JsonArray& acc = root.createNestedArray("acc");

  	// Ajout des données du capteur de force FSR
  	for(int j = 0; j < tabLength; j++){
   		fsr.add(analogRead(fsrPins[j]));
  	}

  	// Données de l'accéléromètre
  	for(int i = 0; i < 3; i++){
  		acc.add(0);
  	}
  	
  	// Stockage des données en format JSON dans la chaîne de caractere JSONchar
  	char buffer[256];
	root.printTo(buffer, sizeof(buffer));
	// root.printTo(Serial);
	jsonChar = buffer;
	// Serial.println(jsonChar);

	
}

char* SMDataManager::sendJsonData(){
  	// Serial.println(jsonChar);

   // for(int j = 0; j <3; j++){
    // acc.add(accArray[j]);
  	// }
  	// root["index"] = i;
  	// root["time"] = 148798+i;
	// Serial.print(jsonChar);
	return jsonChar;
}

