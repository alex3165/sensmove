/*
  SD card test


 */
// include the SD library:
#include <SPI.h>
#include <SD.h>
// include the json library:
#include <ArduinoJson.h>

// set up variables using the SD utility library functions:
Sd2Card card;
SdVolume volume;
SdFile root;
File myFile;
const char* nameFile = "session.txt";
const char* dataToWrite = "[0, 255, 255, 128, 0, 233, 233, 500]";
const int numIteration = 10;
int fsrArray [7] = {0, 255, 255, 128, 0, 233, 233};
int accArray [7] = {0, 255, 255};

// Adafruit SD shields and modules: pin 10
const int chipSelect = 10;

void setup()
{
  // Open serial communications and wait for port to open:
  Serial.begin(9600);
  while (!Serial) {
    ; // wait for serial port to connect.
  }

  Serial.print("\nInitializing SD card...");
  pinMode(10, OUTPUT);     // change this to 53 on a mega


  
if (!SD.begin(10)) {
    Serial.println("initialization failed!");
    return;
 }
  Serial.println("Opening ");
  //Ecrire dans la carte SD
    myFile = SD.open(nameFile, FILE_WRITE);
   if (myFile) {
    Serial.print("Opening file ");
    Serial.println(nameFile);
   } else {
      Serial.print("error opening ");
      Serial.println(nameFile);
   }
  
}

int i = 0;

void loop(void) {
  StaticJsonBuffer<400> jsonBuffer;
  JsonObject& root = jsonBuffer.createObject();
  JsonArray& data = root.createNestedArray("data");
  JsonObject& block = jsonBuffer.createObject();
  root["idDevice"] = "SM1234242";
  root["timeStartSession"] = 201404;
  JsonArray& fsr = block.createNestedArray("fsr");
  JsonArray& acc = block.createNestedArray("acc");
  block["index"] = i;
    block["time"] = 148798;
  
  if(myFile){
      
  
   
   
  for(int j = 0; j <7; j++){
    fsr.add(fsrArray[j]);
  }
   for(int j = 0; j <3; j++){
    acc.add(accArray[j]);
  }
  
    data.add(block);



    if(i<numIteration){
      Serial.print("Writing line ");
      Serial.println(i);
     root.prettyPrintTo(Serial);
     root.PrintTo(myFile);g
     // myFile.println(dataToWrite);
     // Serial.println(dataToWrite);
    }
    if (i==numIteration) {
    
      myFile.close();
      Serial.println("done.");
    }
    i = i+1;
    delay(100);
  }
  
}
