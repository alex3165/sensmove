/**
*  sensmove_accel_poc
*  SensMove
*
*  @author Alexandre Rieux and Jean-Sébastien Pélerin
*  @date 19/02/2015
*  @copyright (c) 2014 SensMove. All rights reserved.
*/

#include <Wire.h>
#include <Adafruit_Sensor.h>
#include <Adafruit_LSM303_U.h>

Adafruit_LSM303_Accel_Unified accel = Adafruit_LSM303_Accel_Unified(54321);

long lastDisplayTime;
float currentLightLevel = 0;

/**
*
* Setup function run once when the program start
*
* @return nothing
*
*/
void setup(void) {
  // monitor read on port 9600
  Serial.begin(9600);

  // set the pin 13 as output
  pinMode(13, OUTPUT);

  /*
  * Try to begin the accelerometer for reading values
  */
  Serial.println("Accelerometer Test"); Serial.println("");
  if(!accel.begin()) {
    // There was a problem detecting the LSM303 ... check your connections
    Serial.println("Ooops, no LSM303 detected ... Check your wiring!");
    while(1);
  }
  
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
  
  /*
  * Get accelerometer values
  */
  sensors_event_t accelEvent;
  accel.getEvent(&accelEvent);
  

  /*
  * Map accelerometers values to the led in analog output 13
  */
  currentLightLevel = map(accelEvent.acceleration.x, -20, 20, 0, 255);
  Serial.print(currentLightLevel); Serial.print(" \n ");
  analogWrite(13, currentLightLevel);
  
  if ((millis() - lastDisplayTime) > 50)
  {
    lastDisplayTime = millis();
  }
}
