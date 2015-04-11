/*
  SMDataManager.h - I/O library for managing sensors value and sending it over bluetooth.
  Created by Alexandre Rieux, March 30, 2015.
  Copyright SensMove.
*/

#ifndef SMSole
#define SMSole

typedef struct Accelerometer {
  float x;
  float y;
  float z;
  time_t unixtimestamp;
} Accelerometer;

typedef struct ForcesResistorsSensors {
  float sensors[];
  time_t unixtimestamp;
} ForcesResistorsSensors;

class SMSole {

  public:

  	SMSole();

    bool isRight;

    // Store the sensors values
    ForcesResistorsSensors fsrValuesBuffer[];
    Accelerometer accelerometerValuesBuffer[];

    // Setters for fsr and accelerometer sensors
    bool setFsrValues(float sensors[], time_t date);
    bool setAccelerometerValues(float accelerometer[], time_t date);

    // Erase values stored in fsrValues array
    bool deleteFsrValues();

    // Erase values in Accelerometer array
    bool deleteAccelerometerValues();

  private:
    
};

#endif