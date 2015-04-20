/*
  SD card test


 The circuit:
  * SD card attached to SPI bus as follows:
 ** MOSI - pin 11 on Arduino Uno/Duemilanove/Diecimila
 ** MISO - pin 12 on Arduino Uno/Duemilanove/Diecimila
 ** CLK - pin 13 on Arduino Uno/Duemilanove/Diecimila
 ** CS - depends on your SD card shield or module.

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

// Adafruit SD shields and modules: pin 10
const int chipSelect = 10;
int i = 0;
void setup()
{
  // Open serial communications and wait for port to open:
  Serial.begin(9600);
  while (!Serial) {
    ; // wait for serial port to connect.
  }


  Serial.println("\nInitializing SD card");
  pinMode(10, OUTPUT);

  
if (!SD.begin(10)) {
    Serial.println("initialization failed!");
    return;
  }
  Serial.println("initialization done.");
 
  //Read in the SD cardfile "session.txt"
  myFile = SD.open(nameFile);
  if (myFile) {
    Serial.print("Opening file ");
    Serial.println(nameFile);
 
    // read from the file until there's nothing else in it:
    while (myFile.available()) {
    	Serial.write(myFile.read());
    }
    // Close the file:
    myFile.close();
  } else {
  	// If the file didn't open, print an error:
    Serial.print("Error opening ");
    Serial.println(nameFile);

  }
}


//Demarrer un enregistrement de session

void loop(void) {

}
