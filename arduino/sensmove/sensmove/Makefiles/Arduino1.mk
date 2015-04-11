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
# Last update: Feb 20, 2015 release 262

WARNING_MESSAGE = 'ARDUINO 1.0.x IS REPLACED BY ARDUINO 1.6.x'


# Adafruit 1.0.x specifics
# Arduino 1.0.x specifics
# LittleRobotsFriends 1.0.x specifics
# ----------------------------------
#
ifneq ($(findstring LITTLEROBOTFRIENDS,$(GCC_PREPROCESSOR_DEFINITIONS)),)
    PLATFORM         := LittleRobotFriends
    PLATFORM_TAG      = ARDUINO=$(LITTLEROBOTFRIENDS_RELEASE) EMBEDXCODE=$(RELEASE_NOW) $(GCC_PREPROCESSOR_DEFINITIONS)
    APPLICATION_PATH := $(LITTLEROBOTFRIENDS_PATH)
    BOARDS_TXT       := $(LITTLEROBOTFRIENDS_BOARDS)
    USER_LIBS_LIST   := $(filter-out 0,$(USER_LIBS_LIST)) LittleRobotFriends
else ifneq ($(findstring ADAFRUIT,$(GCC_PREPROCESSOR_DEFINITIONS)),)
	PLATFORM         := Adafruit
	PLATFORM_TAG      = ARDUINO=$(ARDUINO_RELEASE) ADAFRUIT EMBEDXCODE=$(RELEASE_NOW)
	APPLICATION_PATH := $(ARDUINO_PATH)
	BOARDS_TXT       := $(APPLICATION_PATH)/hardware/arduino/boards.txt
else
    PLATFORM         := Arduino
    PLATFORM_TAG      = ARDUINO=$(ARDUINO_RELEASE) EMBEDXCODE=$(RELEASE_NOW)
    APPLICATION_PATH := $(ARDUINO_PATH)
    BOARDS_TXT       := $(APPLICATION_PATH)/hardware/arduino/boards.txt
endif

APP_TOOLS_PATH   := $(APPLICATION_PATH)/hardware/tools/avr/bin
CORE_LIB_PATH    := $(APPLICATION_PATH)/hardware/arduino/cores/arduino
APP_LIB_PATH     := $(APPLICATION_PATH)/libraries


# Sketchbook/Libraries path
# wildcard required for ~ management
#
ifeq ($(USER_PATH)/Library/Arduino/preferences.txt,)
    $(error Error: run Arduino once and define the sketchbook path)
endif

ifeq ($(wildcard $(SKETCHBOOK_DIR)),)
    SKETCHBOOK_DIR = $(shell grep sketchbook.path $(USER_PATH)/Library/Arduino/preferences.txt | cut -d = -f 2)
endif
ifeq ($(wildcard $(SKETCHBOOK_DIR)),)
   $(error Error: sketchbook path not found)
endif
USER_LIB_PATH  = $(wildcard $(SKETCHBOOK_DIR)/?ibraries)

# Rules for making a c++ file from the main sketch (.pde)
#
PDEHEADER      = \\\#include \"Arduino.h\"  

# Tool-chain names
#
CC      = $(APP_TOOLS_PATH)/avr-gcc
CXX     = $(APP_TOOLS_PATH)/avr-g++
AR      = $(APP_TOOLS_PATH)/avr-ar
OBJDUMP = $(APP_TOOLS_PATH)/avr-objdump
OBJCOPY = $(APP_TOOLS_PATH)/avr-objcopy
SIZE    = $(APP_TOOLS_PATH)/avr-size
NM      = $(APP_TOOLS_PATH)/avr-nm

# Specific AVRDUDE location and options
#
#AVRDUDE_COM_OPTS  = -D -p$(MCU) -C$(AVRDUDE_CONF)
AVRDUDE_COM_OPTS   = -p$(MCU) -C$(AVRDUDE_CONF)

BOARD        = $(call PARSE_BOARD,$(BOARD_TAG),board)
#LDSCRIPT     = $(call PARSE_BOARD,$(BOARD_TAG),ldscript)
# Adafruit Pro Trinket uses arduino:eightanaloginputs
a101         = $(call PARSE_BOARD,$(BOARD_TAG),build.variant)
VARIANT      = $(patsubst arduino:%,%,$(a101))
VARIANT_PATH = $(APPLICATION_PATH)/hardware/arduino/variants/$(VARIANT)

MCU_FLAG_NAME  = mmcu
EXTRA_LDFLAGS  = 
EXTRA_CPPFLAGS = -MMD -I$(VARIANT_PATH) $(addprefix -D, $(PLATFORM_TAG))

# Leonardo USB PID VID
#
USB_TOUCH := $(call PARSE_BOARD,$(BOARD_TAG),upload.protocol)
USB_VID   := $(call PARSE_BOARD,$(BOARD_TAG),build.vid)
USB_PID   := $(call PARSE_BOARD,$(BOARD_TAG),build.pid)

ifneq ($(USB_PID),)
    USB_FLAGS += -DUSB_PID=$(USB_PID)
else
    USB_FLAGS += -DUSB_PID=null
endif

ifneq ($(USB_VID),)
    USB_FLAGS += -DUSB_VID=$(USB_VID)
else
    USB_FLAGS += -DUSB_VID=null
endif

# Serial 1200 reset
#
ifeq ($(USB_TOUCH),avr109)
    USB_RESET  = python $(UTILITIES_PATH)/reset_1200.py
endif
