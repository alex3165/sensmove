#include <Wire.h>
#include <Adafruit_Sensor.h>
#include <Adafruit_LSM303_U.h>

Adafruit_LSM303_Accel_Unified accel = Adafruit_LSM303_Accel_Unified(54321);
float AccelMinX, AccelMaxX;
float AccelMinY, AccelMaxY;
float AccelMinZ, AccelMaxZ;

long lastDisplayTime;

float currentLightLevel = 0;

void setup(void) {
  Serial.begin(9600);
  
  Serial.println("Magnetometer Test"); Serial.println("");
  
  pinMode(13, OUTPUT);
  
  if(!accel.begin())
  {
    /* There was a problem detecting the LSM303 ... check your connections */
    Serial.println("Ooops, no LSM303 detected ... Check your wiring!");
    while(1);
  }
  
  lastDisplayTime = millis();
}

// the loop function runs over and over again forever
void loop(void) {
  
  sensors_event_t accelEvent;
  accel.getEvent(&accelEvent);
  
  currentLightLevel = map(accelEvent.acceleration.x, -20, 20, 0, 255);
  
  Serial.print(currentLightLevel); Serial.print(" \n ");
  
  analogWrite(13, currentLightLevel);
  
  if ((millis() - lastDisplayTime) > 50)
  {
    //Serial.print("Accel Minimums: "); Serial.print(accelEvent.acceleration.x); Serial.print("  ");Serial.print(accelEvent.acceleration.y); Serial.print("  "); Serial.print(accelEvent.acceleration.z); Serial.println();
    lastDisplayTime = millis();
  }
}
