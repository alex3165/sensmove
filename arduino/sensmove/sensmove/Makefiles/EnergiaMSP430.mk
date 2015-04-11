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
# Last update: Jan 24, 2015 release 260



# Energia LaunchPad MSP430 and FR5739 specifics
# ----------------------------------
#
APPLICATION_PATH := $(ENERGIA_PATH)
ENERGIA_RELEASE  := $(shell tail -c2 $(APPLICATION_PATH)/lib/version.txt)
ARDUINO_RELEASE  := $(shell head -c4 $(APPLICATION_PATH)/lib/version.txt | tail -c3)

PLATFORM         := Energia
BUILD_CORE       := msp430
PLATFORM_TAG      = ENERGIA=$(ENERGIA_RELEASE) ARDUINO=$(ARDUINO_RELEASE) EMBEDXCODE=$(RELEASE_NOW) $(filter __%__ ,$(GCC_PREPROCESSOR_DEFINITIONS))

UPLOADER          = mspdebug
UPLOADER_PATH     = $(APPLICATION_PATH)/hardware/tools/msp430/mspdebug
UPLOADER_EXEC     = $(UPLOADER_PATH)/mspdebug
UPLOADER_PROTOCOL = $(call PARSE_BOARD,$(BOARD_TAG),upload.protocol)
UPLOADER_OPTS     = $(UPLOADER_PROTOCOL) --force-reset

# FraunchPad MSP430FR5739 requires a specific command
#
ifeq ($(BOARD_TAG), lpmsp430fr5739)
    UPLOADER_COMMAND = load
else
    UPLOADER_COMMAND = prog
endif

APP_TOOLS_PATH   := $(APPLICATION_PATH)/hardware/tools/msp430/bin
CORE_LIB_PATH    := $(APPLICATION_PATH)/hardware/msp430/cores/msp430
APP_LIB_PATH     := $(APPLICATION_PATH)/hardware/msp430/libraries
BOARDS_TXT       := $(APPLICATION_PATH)/hardware/msp430/boards.txt

# Sketchbook/Libraries path
# wildcard required for ~ management
# ?ibraries required for libraries and Libraries
#
ifeq ($(USER_PATH)/Library/Energia/preferences.txt,)
    $(error Error: run Energia once and define the sketchbook path)
endif

ifeq ($(wildcard $(SKETCHBOOK_DIR)),)
    SKETCHBOOK_DIR = $(shell grep sketchbook.path $(wildcard ~/Library/Energia/preferences.txt) | cut -d = -f 2)
endif
ifeq ($(wildcard $(SKETCHBOOK_DIR)),)
    $(error Error: sketchbook path not found)
endif
USER_LIB_PATH  = $(wildcard $(SKETCHBOOK_DIR)/?ibraries)


# Rules for making a c++ file from the main sketch (.pde)
#
PDEHEADER      = \\\#include \"Energia.h\"  


# Tool-chain names
#
CC      = $(APP_TOOLS_PATH)/msp430-gcc
CXX     = $(APP_TOOLS_PATH)/msp430-g++
AR      = $(APP_TOOLS_PATH)/msp430-ar
OBJDUMP = $(APP_TOOLS_PATH)/msp430-objdump
OBJCOPY = $(APP_TOOLS_PATH)/msp430-objcopy
SIZE    = $(APP_TOOLS_PATH)/msp430-size
NM      = $(APP_TOOLS_PATH)/msp430-nm

BOARD          = $(call PARSE_BOARD,$(BOARD_TAG),board)
#LDSCRIPT = $(call PARSE_BOARD,$(BOARD_TAG),ldscript)
VARIANT        = $(call PARSE_BOARD,$(BOARD_TAG),build.variant)
VARIANT_PATH   = $(APPLICATION_PATH)/hardware/msp430/variants/$(VARIANT)

OPTIMISATION   = -Os

MCU_FLAG_NAME  = mmcu
EXTRA_LDFLAGS  =
#EXTRA_LDFLAGS = -T$(CORE_LIB_PATH)/$(LDSCRIPT)
EXTRA_CPPFLAGS = $(addprefix -D, $(PLATFORM_TAG)) -I$(VARIANT_PATH)

