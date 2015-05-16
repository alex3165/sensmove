/**
*  sensmove_fsr_poc
*  SensMove
*
*  @author Jean-Sébastien Pélerin
*  @date 20/12/2014
*  @copyright (c) 2014 SensMove. All rights reserved.
*/
///screen dev/tty.usbmodemfd121
#include <Wire.h>
#include <Adafruit_Sensor.h>
#include <Adafruit_LSM303_U.h>

Adafruit_LSM303_Accel_Unified accel = Adafruit_LSM303_Accel_Unified(54321);

long lastDisplayTime;
float accelX = 0;
float accelY = 0;
float accelZ = 0;


int fsrAnalogPin1 = 6; // FSR is connected to analog 6
int fsrAnalogPin2 = 0; // FSR is connected to analog 0
int fsrAnalogPin3 = 2; // FSR is connected to analog 0
int fsrAnalogPin4 = 3; // FSR is connected to analog 0
int fsrAnalogPin5 = 5; // FSR is connected to analog 0
int fsrAnalogPin6 = 4; // FSR is connected to analog 4
int fsrAnalogPin7 = 1; // FSR is connected to analog 1

int LEDpin = 13;      // connect Red LED to pin 13 (PWM pin)
int fsrReading1;      // the analog reading from the FSR resistor divider
int fsrReading2;      // the analog reading from the FSR resistor divider
int fsrReading3;      // the analog reading from the FSR resistor divider
int fsrReading4;      // the analog reading from the FSR resistor divider
int fsrReading5;      // the analog reading from the FSR resistor divider
int fsrReading6;      // the analog reading from the FSR resistor divider
int fsrReading7;      // the analog reading from the FSR resistor divider
int maxfsr;
int maxfsr1;
int maxfsr2;
int maxfsr3;
int maxfsr4;

int LEDbrightness;

/**
*
* Setup function run once when the program start
*
* @return nothing
*
*/
void setup(void) {
   Serial.begin(9600);

  pinMode(LEDpin, OUTPUT);
  //if(!accel.begin()) {
    // There was a problem detecting the LSM303 ... check your connections
    //Serial.println("Ooops, no LSM303 detected ... Check your wiring!");
    //while(1);
 // }
  lastDisplayTime = millis();

}

/**
*
* loop function run again and again
*
* @return nothing
*
*/
void loop(void) {
  
  sensors_event_t accelEvent;
 // accel.getEvent(&accelEvent);
 // accelX = map(accelEvent.acceleration.x, -20, 20, 0, 255);
 // accelY = map(accelEvent.acceleration.y, -20, 20, 0, 255);
 // accelZ = map(accelEvent.acceleration.z, -20, 20, 0, 255);

  fsrReading1 = analogRead(fsrAnalogPin1);
  fsrReading2 = analogRead(fsrAnalogPin2);
  fsrReading3 = analogRead(fsrAnalogPin3);
  fsrReading4 = analogRead(fsrAnalogPin4);
  fsrReading5 = analogRead(fsrAnalogPin5);
  fsrReading6 = analogRead(fsrAnalogPin6);
  fsrReading7 = analogRead(fsrAnalogPin7);
  
  Serial.print("S1 : ");
  Serial.print(fsrReading1);
  Serial.print("  S2 :");
  Serial.print(fsrReading2);
  Serial.print("  S3 : ");
  Serial.print(fsrReading3);
  Serial.print("  S4 : ");
  Serial.print(fsrReading4);
  Serial.print("  S5 : ");
  Serial.print(fsrReading5);
  Serial.print("  S6 : ");
  Serial.print(fsrReading6);
  Serial.print("  S7 : ");
  Serial.print(fsrReading7);
  Serial.print(" Ax : ");
  Serial.print(accelX);
  Serial.print(" Ay : ");
  Serial.print(accelY); 
  Serial.print(" Az : ");
  Serial.print(accelZ); 

  maxfsr1 = max(fsrReading1,fsrReading2);
  maxfsr2 = max(fsrReading3,fsrReading4);
  maxfsr3 = max(fsrReading5,fsrReading6);
  
  maxfsr4 = max(maxfsr1,fsrReading7);
  maxfsr1 = max(maxfsr2,maxfsr3);
  
  maxfsr = max(maxfsr1,maxfsr4);
  // we'll need to change the range from the analog reading (0-1023) down to the range
  // used by analogWrite (0-255) with map!
  LEDbrightness = map(maxfsr, 0, 1023, 0, 255);

  // LED gets brighter the harder you press
  analogWrite(LEDpin, LEDbrightness);
 Serial.println();
  delay(100); // change the frequency of loop

}
