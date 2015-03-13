/**
*  sensmove_double_fsr
*  SensMove
*
*  @author Alexandre Rieux
*  @date 19/02/2015
*  @copyright (c) 2014 SensMove. All rights reserved.
*/

int firstSensorValue;
int secondSensorValue;

/**
*
* Setup function run once when the program start
*
* @return nothing
*
*/
void setup()
{
	Serial.begin(9600);
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
	firstSensorValue = analogRead(0);
	secondSensorValue = analogRead(1);

  	Serial.println("First sensor value : " + firstSensorValue + " Second sensor : " + secondSensorPin);
}