#!/usr/bin/env python

import serial
import sys
import time

if len(sys.argv) < 2:
    print "Missing serial port"
    sys.exit()

print 'Setting %s at 1200' % sys.argv[1]

ser = serial.Serial(sys.argv[1], baudrate=1200)

time.sleep(1)

ser.close()
