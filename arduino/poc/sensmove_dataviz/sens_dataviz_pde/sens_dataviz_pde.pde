import processing.serial.*;

Serial myPort;
String[] tempSensorValues;
Integer[] sensorValues = new Integer[7];

String test = "102,300,130,20,88,91,43,1";

void setup() {
  size(1024, 720);
  smooth();
  background(255);
  String portName = Serial.list()[0];
  myPort = new Serial(this, portName, 9600);
}

void draw() {
  //if ( myPort.available() > 0) {
    //String tempValue = myPort.readStringUntil('\n');
  //}
  color(0);
  
  tempSensorValues = split(test, ',');
  
  for (int i = 0; i < tempSensorValues.length - 1; i++) {
    int temp = Integer.parseInt(tempSensorValues[i]);
    sensorValues[i] = temp;
  }
  
  for (int i = 0; i <= sensorValues.length - 1; i++) {
    // println(sensorValues[i]);
    line( (width/sensorValues.length)*i + 10, height, (width/sensorValues.length)*i + 10, sensorValues[i] );
  }
}
