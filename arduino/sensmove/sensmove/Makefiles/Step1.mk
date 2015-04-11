#
# embedXcode
# ----------------------------------
# Embedded Computing on Xcode
#
# Copyright © Rei VILO, 2010-2015
# http://embedxcode.weebly.com
# All rights reserved
#
#
# Last update: Apr 01, 2015 release 274



# Sketch unicity test and extension
# ----------------------------------
#
ifndef SKETCH_EXTENSION
    ifeq ($(words $(wildcard *.pde) $(wildcard *.ino)), 0)
        $(error No pde or ino sketch)
    endif

    ifneq ($(words $(wildcard *.pde) $(wildcard *.ino)), 1)
        $(error More than 1 pde or ino sketch)
    endif

    ifneq ($(wildcard *.pde),)
        SKETCH_EXTENSION := pde
    else ifneq ($(wildcard *.ino),)
        SKETCH_EXTENSION := ino
    else
        $(error Extension error)
    endif
endif

ifneq ($(MULTI_INO),1)
ifneq ($(SKETCH_EXTENSION),__main_cpp_only__)
    ifneq ($(SKETCH_EXTENSION),_main_cpp_only_)
        ifneq ($(SKETCH_EXTENSION),cpp)
            ifeq ($(words $(wildcard *.$(SKETCH_EXTENSION))), 0)
                $(error No $(SKETCH_EXTENSION) sketch)
            endif

            ifneq ($(words $(wildcard *.$(SKETCH_EXTENSION))), 1)
                $(error More than one $(SKETCH_EXTENSION) sketch)
            endif
        endif
    endif
endif
endif

# Board selection
# ----------------------------------
# Board specifics defined in .xconfig file
# BOARD_TAG and AVRDUDE_PORT 
#
ifneq ($(MAKECMDGOALS),boards)
    ifneq ($(MAKECMDGOALS),clean)
        ifndef BOARD_TAG
            $(error BOARD_TAG not defined)
        endif
    endif
endif

ifndef BOARD_PORT
    BOARD_PORT = /dev/tty.usb*
endif


# Path to applications folder
#
USER_PATH      := $(wildcard ~)
EMBEDXCODE_APP  = $(USER_PATH)/Library/embedXcode
PARAMETERS_TXT  = $(EMBEDXCODE_APP)/parameters.txt

ifndef APPLICATIONS_PATH
    APPLICATIONS_PATH = /Applications
endif

# APPlications full paths
#
ARDUINO_APP   = $(APPLICATIONS_PATH)/Arduino.app
MPIDE_APP     = $(APPLICATIONS_PATH)/Mpide.app
WIRING_APP    = $(APPLICATIONS_PATH)/Wiring.app
ENERGIA_APP   = $(APPLICATIONS_PATH)/Energia.app
MAPLE_APP     = $(APPLICATIONS_PATH)/MapleIDE.app
ADAFRUIT_APP  = $(APPLICATIONS_PATH)/Adafruit.app
SPARK_APP     = $(EMBEDXCODE_APP)/Spark
MBED_APP      = $(EMBEDXCODE_APP)/mbed

# panStamp.app path
#
PANSTAMP_0    = $(APPLICATIONS_PATH)/panStamp.app
ifneq ($(wildcard $(PANSTAMP_0)),)
    PANSTAMP_APP    = $(PANSTAMP_0)
else
    PANSTAMP_APP    = $(APPLICATIONS_PATH)/Arduino.app
endif

# Teensyduino.app path
#
TEENSY_0    = $(APPLICATIONS_PATH)/Teensyduino.app
ifneq ($(wildcard $(TEENSY_0)),)
    TEENSY_APP    = $(TEENSY_0)
else
    TEENSY_APP    = $(APPLICATIONS_PATH)/Arduino.app
endif

# DigisparkArduino.app path
#
DIGISPARK_0 = $(APPLICATIONS_PATH)/DigisparkArduino.app
ifneq ($(wildcard $(DIGISPARK_0)),)
    DIGISPARK_APP = $(DIGISPARK_0)
else
    DIGISPARK_APP = $(APPLICATIONS_PATH)/Arduino.app
endif

# Microduino.app path
#
MICRODUINO_0 = $(APPLICATIONS_PATH)/Microduino.app
ifneq ($(wildcard $(MICRODUINO_0)),)
    MICRODUINO_APP = $(MICRODUINO_0)
else
    MICRODUINO_APP = $(APPLICATIONS_PATH)/Arduino.app
endif

# IntelArduino.app path
#
GALILEO_0    = $(APPLICATIONS_PATH)/IntelArduino.app
ifneq ($(wildcard $(GALILEO_0)),)
    GALILEO_APP    = $(GALILEO_0)
else
    GALILEO_APP    = $(APPLICATIONS_PATH)/Arduino.app
endif

# RedBearLab.app path
#
REDBEARLAB_0    = $(APPLICATIONS_PATH)/RedBearLab.app
ifneq ($(wildcard $(REDBEARLAB_0)),)
    REDBEARLAB_APP    = $(REDBEARLAB_0)
else
    REDBEARLAB_APP    = $(APPLICATIONS_PATH)/Arduino.app
endif

# LittleRobotFriends.app path
#
LITTLEROBOTFRIENDS_0 = $(APPLICATIONS_PATH)/LittleRobotFriends.app
ifneq ($(wildcard $(LITTLEROBOTFRIENDS_0)),)
    LITTLEROBOTFRIENDS_APP    = $(LITTLEROBOTFRIENDS_0)
else
    LITTLEROBOTFRIENDS_APP    = $(APPLICATIONS_PATH)/Arduino.app
endif

ifeq ($(wildcard $(ARDUINO_APP)),)
    ifeq ($(wildcard $(MPIDE_APP)),)
        ifeq ($(wildcard $(WIRING_APP)),)
            ifeq ($(wildcard $(ENERGIA_APP)),)
                ifeq ($(wildcard $(MAPLE_APP)),)
                    ifeq ($(wildcard $(TEENSY_APP)),)
                        ifeq ($(wildcard $(DIGISPARK_APP)),)
                            ifeq ($(wildcard $(MICRODUINO_APP)),)
                                    ifeq ($(wildcard $(GALILEO_APP)),)
                                        ifeq ($(wildcard $(SPARK_APP)),)
                                                ifeq ($(wildcard $(REDBEARLAB_APP)),)
                                                    ifeq ($(wildcard $(LITTLEROBOTFRIENDS_APP)),)
                                                        ifeq ($(wildcard $(ADAFRUIT_APP)),)
                                                            $(error Error: no application found)
                                                        endif
                                                    endif
                                                endif
                                        endif
                                    endif
                            endif
                        endif
                    endif
                endif
            endif
        endif
    endif
endif


# Arduino-related nightmares
# ----------------------------------
#
# Welcome single release 1.6!
# Gone Arduino 1.0, 1.5 Java 6 and 1.5 Java 7 triple release nightmare
#
ifneq ($(wildcard $(ARDUINO_APP)),) # */
    s102 = $(ARDUINO_APP)/Contents/Resources/Java/lib/version.txt
    s103 = $(ARDUINO_APP)/Contents/Java/lib/version.txt
    ifneq ($(wildcard $(s102)),)
        ARDUINO_RELEASE := $(shell cat $(s102) | sed -e "s/\.//g")
    else
        ARDUINO_RELEASE := $(shell cat $(s103) | sed -e "s/\.//g")
    endif
    ARDUINO_MAJOR := $(shell echo $(ARDUINO_RELEASE) | cut -d. -f 1-2)
else
    ARDUINO_RELEASE := 0
    ARDUINO_MAJOR   := 0
endif

# But nightmare continues with 2 releases for Arduino 1.6
# Different folder locations in Java 6 and Java 7 versions
# Another example of Arduino's quick and dirty job :(
#
ifeq ($(wildcard $(ARDUINO_APP)/Contents/Resources/Java),)
    ARDUINO_PATH   := $(ARDUINO_APP)/Contents/Java
else
    ARDUINO_PATH   := $(ARDUINO_APP)/Contents/Resources/Java
endif

# Same for RedBearLab plug-in for Arduino 1.5.x
#
ifeq ($(wildcard $(REDBEARLAB_APP)/Contents/Resources/Java),)
    REDBEARLAB_PATH   := $(REDBEARLAB_APP)/Contents/Java
else
    REDBEARLAB_PATH   := $(REDBEARLAB_APP)/Contents/Resources/Java
endif

# Same for LittleRobotFriends plug-in for Arduino 1.0.x
#
ifneq ($(findstring LITTLEROBOTFRIENDS,$(GCC_PREPROCESSOR_DEFINITIONS)),)

    LITTLEROBOTFRIENDS_RELEASE := $(shell find $(LITTLEROBOTFRIENDS_APP) -name version.txt -exec cat {} \; | sed -e "s/\.//g")
    LITTLEROBOTFRIENDS_MAJOR := $(shell find $(LITTLEROBOTFRIENDS_APP) -name version.txt -exec cat {} \; | cut -d. -f 1-2 | sed -e "s/\.//g")

    ifeq ($(wildcard $(LITTLEROBOTFRIENDS_APP)),)
        $(error Error: no application found)
    else
        ifeq ($(LITTLEROBOTFRIENDS_MAJOR),10)
            s101 = $(shell grep sketchbook.path $(USER_PATH)/Library/Arduino/preferences.txt | cut -d = -f 2)
            LITTLEROBOTFRIENDS_BOARDS = $(wildcard $(s101)/?ardware/LittleRobotFriends/boards.txt)
        else
#            s101 = $(shell grep sketchbook.path $(USER_PATH)/Library/Arduino15/preferences.txt | cut -d = -f 2)
#            LITTLEROBOTFRIENDS_PATH = $(wildcard $(s101)/?ardware/LittleRobotFriends/avr)
            $(error Little Robot Friends requires Arduino 1.0)
        endif
    endif
endif

# Same for Teensyduino plug-in for Arduino 1.6.x
#
ifeq ($(wildcard $(TEENSY_APP)/Contents/Resources/Java),)
	TEENSY_PATH   := $(TEENSY_APP)/Contents/Java
else
	TEENSY_PATH   := $(TEENSY_APP)/Contents/Resources/Java
endif

# Paths list for other genuine IDEs
#
MPIDE_PATH      = $(MPIDE_APP)/Contents/Resources/Java
WIRING_PATH     = $(WIRING_APP)/Contents/Java
ENERGIA_PATH    = $(ENERGIA_APP)/Contents/Resources/Java
MAPLE_PATH      = $(MAPLE_APP)/Contents/Resources/Java
GALILEO_PATH    = $(GALILEO_APP)/Contents/Resources/Java

# Paths list for other plug-ins
#
DIGISPARK_PATH  = $(DIGISPARK_APP)/Contents/Resources/Java
MICRODUINO_PATH = $(MICRODUINO_APP)/Contents/Resources/Java
ADAFRUIT_PATH   = $(ADAFRUIT_APP)/Contents/Resources/Java
PANSTAMP_PATH   = $(PANSTAMP_APP)/Contents/Resources/Java
LITTLEROBOTFRIENDS_PATH = $(LITTLEROBOTFRIENDS_APP)/Contents/Resources/Java

# Paths list for IDE-less platforms
#
SPARK_PATH      = $(EMBEDXCODE_APP)/Spark
MBED_APP        = $(EMBEDXCODE_APP)/mbed


# Miscellaneous
# ----------------------------------
# Variables
#
TARGET      := embeddedcomputing
USER_FLAG   := false
TEMPLATE    := ePsiEJEtRXnDNaFGpywBX9vzeNQP4vUb

# Builds directory
#
OBJDIR  = Builds

# Function PARSE_BOARD data retrieval from boards.txt
# result = $(call PARSE_BOARD 'boardname','parameter')
#
PARSE_BOARD = $(shell if [ -f $(BOARDS_TXT) ]; then grep ^$(1).$(2)= $(BOARDS_TXT) | cut -d = -f 2-; fi; )

# Function PARSE_FILE data retrieval from specified file
# result = $(call PARSE_FILE 'boardname','parameter','filename')
#
PARSE_FILE = $(shell if [ -f $(3) ]; then grep ^$(1).$(2) $(3) | cut -d = -f 2-; fi; )


# Clean if new BOARD_TAG
# ----------------------------------
#
NEW_TAG := $(strip $(OBJDIR)/$(BOARD_TAG)-TAG) #
OLD_TAG := $(strip $(wildcard $(OBJDIR)/*-TAG)) # */

ifneq ($(OLD_TAG),$(NEW_TAG))
    CHANGE_FLAG := 1
else
    CHANGE_FLAG := 0
endif


# Identification and switch
# ----------------------------------
# Look if BOARD_TAG is listed as a Arduino/Arduino board
# Look if BOARD_TAG is listed as a Arduino/arduino/avr board *1.5
# Look if BOARD_TAG is listed as a Arduino/arduino/sam board *1.5
# Look if BOARD_TAG is listed as a Mpide/PIC32 board
# Look if BOARD_TAG is listed as a Wiring/Wiring board
# Look if BOARD_TAG is listed as a Energia/MPS430 board
# Look if BOARD_TAG is listed as a MapleIDE/LeafLabs board
# Look if BOARD_TAG is listed as a Teensy/Teensy board
# Look if BOARD_TAG is listed as a Microduino/Microduino board
# Look if BOARD_TAG is listed as a Digispark/Digispark board
# Look if BOARD_TAG is listed as a IntelGalileo/arduino/x86 board
# Look if BOARD_TAG is listed as a Adafruit/Arduino board
# Look if BOARD_TAG is listed as a LittleRobotFriends board
# Look if BOARD_TAG is listed as a mbed board
# Look if BOARD_TAG is listed as a RedBearLab/arduino/RBL_nRF51822 board
# Look if BOARD_TAG is listed as a Spark board
#
# Order matters!
#
ifneq ($(MAKECMDGOALS),boards)
    ifneq ($(MAKECMDGOALS),clean)
        # Arduino
        ifneq ($(call PARSE_FILE,$(BOARD_TAG),name,$(ARDUINO_PATH)/hardware/arduino/boards.txt),)
            include $(MAKEFILE_PATH)/Arduino.mk
        else ifneq ($(call PARSE_FILE,$(BOARD_TAG),name,$(ARDUINO_PATH)/hardware/arduino/avr/boards.txt),)
            include $(MAKEFILE_PATH)/Arduino15avr.mk
        else ifneq ($(call PARSE_FILE,$(BOARD_TAG1),name,$(ARDUINO_PATH)/hardware/arduino/avr/boards.txt),)
            include $(MAKEFILE_PATH)/Arduino15avr.mk
        else ifneq ($(call PARSE_FILE,$(BOARD_TAG),name,$(ARDUINO_PATH)/hardware/arduino/sam/boards.txt),)
            include $(MAKEFILE_PATH)/Arduino15sam.mk
        else ifneq ($(call PARSE_FILE,$(BOARD_TAG),name,$(ARDUINO_PATH)/hardware/arduino/boards.txt),)
            include $(MAKEFILE_PATH)/Arduino.mk

        # Intel
        else ifneq ($(call PARSE_FILE,$(BOARD_TAG),name,$(GALILEO_PATH)/hardware/intel/i586-uclibc/boards.txt),)
            include $(MAKEFILE_PATH)/Galileo.mk
        else ifneq ($(call PARSE_FILE,$(BOARD_TAG),name,$(GALILEO_PATH)/hardware/intel/i686/boards.txt),)
            include $(MAKEFILE_PATH)/Edison.mk

        # panStamp
        else ifneq ($(call PARSE_FILE,$(BOARD_TAG),name,$(PANSTAMP_PATH)/hardware/panstamp/avr/boards.txt),)
            include $(MAKEFILE_PATH)/panStampAVR.mk
        else ifneq ($(call PARSE_FILE,$(BOARD_TAG),name,$(PANSTAMP_PATH)/hardware/panstamp/msp430/boards.txt),)
            include $(MAKEFILE_PATH)/panStampNRG.mk

        # MPIDE
        else ifneq ($(call PARSE_FILE,$(BOARD_TAG),name,$(MPIDE_PATH)/hardware/pic32/boards.txt),)
            include $(MAKEFILE_PATH)/Mpide.mk
        else ifneq ($(shell grep -rnwls $(MPIDE_PATH)/hardware/pic32/variants -e '$(BOARD_TAG).name'),)
            include $(MAKEFILE_PATH)/Mpide.mk

        # Energia
        else ifneq ($(call PARSE_FILE,$(BOARD_TAG),name,$(ENERGIA_PATH)/hardware/msp430/boards.txt),)
            include $(MAKEFILE_PATH)/EnergiaMSP430.mk
        else ifneq ($(call PARSE_FILE,$(BOARD_TAG),name,$(ENERGIA_PATH)/hardware/c2000/boards.txt),)
            include $(MAKEFILE_PATH)/EnergiaC2000.mk
        else ifneq ($(call PARSE_FILE,$(BOARD_TAG),name,$(ENERGIA_PATH)/hardware/lm4f/boards.txt),)
            include $(MAKEFILE_PATH)/EnergiaLM4F.mk
        else ifneq ($(call PARSE_FILE,$(BOARD_TAG),name,$(ENERGIA_PATH)/hardware/cc3200/boards.txt),)
            include $(MAKEFILE_PATH)/EnergiaCC3200.mk
        else ifneq ($(call PARSE_FILE,$(BOARD_TAG),name,$(ENERGIA_PATH)/hardware/msp432/boards.txt),)
            include $(MAKEFILE_PATH)/EnergiaMSP432.mk

        # Others
        else ifneq ($(call PARSE_FILE,$(BOARD_TAG),name,$(ADAFRUIT_PATH)/hardware/arduino/boards.txt),)
            ARDUINO_PATH := $(ADAFRUIT_PATH)
            include $(MAKEFILE_PATH)/Arduino1.mk

        else ifneq ($(call PARSE_FILE,$(BOARD_TAG),name,$(LITTLEROBOTFRIENDS_BOARDS)),)
            include $(MAKEFILE_PATH)/Arduino1.mk
        else ifneq ($(call PARSE_FILE,$(BOARD_TAG),name,$(REDBEARLAB_PATH)/hardware/arduino/RBL_nRF51822/boards.txt),)
            include $(MAKEFILE_PATH)/RedBearLab.mk

        else ifneq ($(call PARSE_FILE,$(BOARD_TAG),name,$(MAPLE_PATH)/hardware/leaflabs/boards.txt),)
            include $(MAKEFILE_PATH)/MapleIDE.mk
        else ifneq ($(call PARSE_FILE,$(BOARD_TAG),name,$(WIRING_PATH)/hardware/Wiring/boards.txt),)
            include $(MAKEFILE_PATH)/Wiring.mk
        else ifneq ($(call PARSE_FILE,$(BOARD_TAG),name,$(TEENSY_PATH)/hardware/teensy/avr/boards.txt),)
            include $(MAKEFILE_PATH)/Teensy.mk
        else ifneq ($(call PARSE_FILE,$(BOARD_TAG),name,$(MICRODUINO_PATH)/hardware/Microduino/boards.txt),)
            include $(MAKEFILE_PATH)/Microduino.mk
        else ifneq ($(call PARSE_FILE,$(BOARD_TAG),name,$(DIGISPARK_PATH)/hardware/digistump/boards.txt),)
            include $(MAKEFILE_PATH)/Digispark.mk

#        else ifneq ($(call PARSE_FILE,$(BOARD_TAG),name,$(MBED_PATH)/boards.txt),)
#            include $(MAKEFILE_PATH)/mbed.mk
        else ifeq ($(filter MBED,$(GCC_PREPROCESSOR_DEFINITIONS)),MBED)
            include $(MAKEFILE_PATH)/mbed.mk

        else ifneq ($(call PARSE_FILE,$(BOARD_TAG),name,$(REDBEARLAB_PATH)/hardware/arduino/RBL_nRF51822/boards.txt),)
            include $(MAKEFILE_PATH)/RedBearLab.mk

        else ifeq ($(filter SPARK,$(GCC_PREPROCESSOR_DEFINITIONS)),SPARK)
            include $(MAKEFILE_PATH)/Spark.mk


        else
            $(error $(BOARD_TAG) board is unknown)
        endif
    endif
endif


# List of sub-paths to be excluded
#
EXCLUDE_NAMES  = Example example Examples examples Archive archive Archives archives Documentation documentation Reference reference
EXCLUDE_NAMES += ArduinoTestSuite
EXCLUDE_NAMES += $(EXCLUDE_LIBS)
EXCLUDE_LIST   = $(addprefix %,$(EXCLUDE_NAMES))

# Step 2
#
include $(MAKEFILE_PATH)/Step2.mk

