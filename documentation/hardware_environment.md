# Hardware

We are developping all the hardware logic on Arduino. We are using Arduino library system for our development for a cleaner project, then the main Arduino file is at the path: program/program.ino. For a better libraries management we developed a small bash script that is create a symbolic link for every library on libraries folder into the Arduino home folder.

If you want to improve data transfer speed between insole system and smartphone app you should decrease the delay in the arduino main file.
