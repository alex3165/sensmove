# Hardware

We are developping all the hardware logic on Arduino. We are using Arduino library system for our development for a cleaner project, then the main Arduino file is at the path: program/program.ino. For a better librariy management we developed a small bash script that creates a symbolic link for each library on libraries folder into the Arduino home folder.

If you want to improve data transfer speed between insole system and smartphone app you should decrease the delay in the arduino main file.

## More

For further improvement of the device, we are planning to add an accelerometer to the smartsole. In out tests we already have chosen the module [Compass LSM303DLHC](https://www.adafruit.com/products/1120). This new feature will allow us to detect steps, jumps and the pace of the user.