/**
*  sensmove_fsr_poc
*  SensMove
*
*  @author Jean-Sébastien Pélerin
*  @date 20/12/2014
*  @copyright (c) 2014 SensMove. All rights reserved.
*/
 
int fsrAnalogPin = 0; // FSR is connected to analog 0
int LEDpin = 13;      // connect Red LED to pin 11 (PWM pin)
int fsrReading;      // the analog reading from the FSR resistor divider
int LEDbrightness;

/**
*
* Setup function run once when the program start
*
* @return nothing
*
*/
void setup(void) {
  Serial.begin(9600);   // We'll send debugging information via the Serial monitor
  pinMode(LEDpin, OUTPUT);
}

/**
*
* loop function run again and again
*
* @return nothing
*
*/
void loop(void) {
  fsrReading = analogRead(fsrAnalogPin);
  Serial.print("Analog reading = ");
  Serial.println(fsrReading);
 
  // we'll need to change the range from the analog reading (0-1023) down to the range
  // used by analogWrite (0-255) with map!
  LEDbrightness = map(fsrReading, 0, 1023, 0, 255);
  // LED gets brighter the harder you press
  analogWrite(LEDpin, LEDbrightness);
 
  delay(100); // change the frequency of loop
}
