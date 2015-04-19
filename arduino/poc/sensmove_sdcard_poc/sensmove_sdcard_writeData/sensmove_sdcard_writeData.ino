/*
  SD card test


 */
// include the SD library:
#include <SPI.h>
#include <SD.h>

// set up variables using the SD utility library functions:
Sd2Card card;
SdVolume volume;
SdFile root;
File myFile;
const char* nameFile = "session.txt";
const char* dataToWrite = "0, 255, 255, 128, 0, 233, 233, 500, 333, 443, 10";
const int numIteration = 10;

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
  
  if(myFile){
    
    if(i<numIteration){
      Serial.print("Writing line ");
      Serial.println(i);

      myFile.println(dataToWrite);
      Serial.println(dataToWrite);
    }
    if (i==numIteration) {
    
      myFile.close();
      Serial.println("done.");
    }
    i = i+1;
    delay(100);
  }
  
}
