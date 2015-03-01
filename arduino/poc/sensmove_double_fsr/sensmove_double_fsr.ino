int firstSensorPin = 0;
int secondSensorPin = 1;


int firstSensorValue;
int secondSensorValue;

void setup()
{
	Serial.begin(9600);
}

void loop()
{
	firstSensorValue = analogRead(firstSensorPin);
	secondSensorValue = analogRead(secondSensorPin);

  	Serial.println("First sensor value : " + firstSensorValue + " Second sensor : " + secondSensorPin);
}