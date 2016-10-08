# Hardware

We are developping all the hardware of the product using Arduino. We are mainly using Arduino's libraries as development architecture for a cleaner project structure. The main Arduino file is at the path: program/program.ino. For a better librariy management we developed a small bash script which creates a symlink for each library on Arduino's home folder.

You can change the data transfer speed rate in the entry file of the Arduino program.

## More

For further improvements of the device, we are planning to add an accelerometer to the smartsole. For testing purpose we have chosed the [Compass LSM303DLHC](https://www.adafruit.com/products/1120). This new feature will allow us to detect steps, jumps and the pace of the user.
