/*
* SD card test
*
*
* The circuit:
*  * SD card attached to SPI bus as follows:
* ** MOSI - pin 11 on Arduino Uno/Duemilanove/Diecimila
* ** MISO - pin 12 on Arduino Uno/Duemilanove/Diecimila
* ** CLK - pin 13 on Arduino Uno/Duemilanove/Diecimila
* ** CS - depends on your SD card shield or module.
*
*/


/*
*  Dependencies
*/
#include <SPI.h>
#include <SD.h>
#include <ArduinoJson.h> // include the json library:


// set up variables using the SD utility library functions:
//Sd2Card card;
//SdVolume volume;
//SdFile root;
File myFile;
 char* nameFile = "session.txt";
//const char* dataToWrite = "[0, 255, 255, 128, 0, 233, 233, 500]";
const int numIteration = 100;
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
  if(SD.remove(nameFile)){
        Serial.println("File deleted");

  }//suppression du contenu du fichier avant de le remplir
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
int sensor1 = analogRead(0);
void loop(void) {
  StaticJsonBuffer<400> jsonBuffer;
  JsonObject& root = jsonBuffer.createObject();
  //JsonArray& data = root.createNestedArray("data");
  JsonObject& block = jsonBuffer.createObject();
  root["idDevice"] = "SM1234242";
  root["timeStartSession"] = 201404;
  JsonArray& fsr = root.createNestedArray("fsr");
  JsonArray& acc = root.createNestedArray("acc");
  root["index"] = i;
  root["time"] = 148798+i;
  
  if(myFile){
      
   
  for(int j = 0; j <7; j++){
     fsrArray[j] = analogRead(0);

    fsr.add(fsrArray[j]);
  }
   for(int j = 0; j <3; j++){
    acc.add(accArray[j]);
  }
  
 //   data.add(block);



    if(i<numIteration){
      //Serial.print("Writing line ");
      //Serial.println(i);
     root.prettyPrintTo(Serial);
     root.printTo(myFile);
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
