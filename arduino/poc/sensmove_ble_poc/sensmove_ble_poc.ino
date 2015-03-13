/**
*  sensmove_ble_poc
*  SensMove
*
*  @author Alexandre Rieux
*  @date 12/01/2015
*  @copyright (c) 2014 SensMove. All rights reserved.
*/

#include <SPI.h>
#include "Adafruit_BLE_UART.h"

#define ADAFRUITBLE_REQ 10
#define ADAFRUITBLE_RDY 2
#define ADAFRUITBLE_RST 9

Adafruit_BLE_UART BTLEserial = Adafruit_BLE_UART(ADAFRUITBLE_REQ, ADAFRUITBLE_RDY, ADAFRUITBLE_RST);
aci_evt_opcode_t laststatus = ACI_EVT_DISCONNECTED;

int fsrAnalogPin = 0; // FSR is connected to analog 0
int fsrReading;
int fsrReaded;

/**
*
* Setup function run once when the program start
*
* @return nothing
*
*/
void setup(void)
{ 
  Serial.begin(9600);
  while(!Serial); // Leonardo/Micro should wait for serial init
  Serial.println(F("Sensmove Force Sensor Resistor send datas over bluetooth POC"));

  BTLEserial.setDeviceName("SENSMOVE"); /* 7 characters max! */

  BTLEserial.begin();
}

/**
*
* loop function run again and again
*
* @return nothing
*
*/
void loop()
{
  // Tell the nRF8001 to do whatever it should be working on.
  BTLEserial.pollACI();

  // Ask what is our current status
  aci_evt_opcode_t status = BTLEserial.getState();

  if (status != laststatus) {  // If the status changed....
    // print it out!
    if (status == ACI_EVT_DEVICE_STARTED) {
        Serial.println(F("* Advertising started"));
    }
    if (status == ACI_EVT_CONNECTED) {
        Serial.println(F("* Connected!"));
    }
    if (status == ACI_EVT_DISCONNECTED) {
        Serial.println(F("* Disconnected or advertising timed out"));
    }
    // OK set the last status change to this one
    laststatus = status;
  }

  if (status == ACI_EVT_CONNECTED) { // If phone device is connected
    
    /*
    *   To receiving datas
    */
    if (BTLEserial.available()) {
      Serial.print("* "); Serial.print(BTLEserial.available()); Serial.println(F(" bytes available from BTLE"));
    }
    // OK while we still have something to read, get a character and print it out
    while (BTLEserial.available()) {
      char c = BTLEserial.read();
      Serial.print(c);
    }


    /*
    *   To send datas
    */
    fsrReading = analogRead(fsrAnalogPin);

    // Test if the sensor value changed
    if (fsrReading != fsrReaded) {
      Serial.print("Send : ");
      Serial.print(fsrReading);
      BTLEserial.print(fsrReading);
    }
    
    fsrReaded = analogRead(fsrAnalogPin);
  }

}
