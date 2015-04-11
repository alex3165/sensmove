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
# Last update: Mar 02, 2015 release 263

ifneq ($(shell grep 1.5 $(ARDUINO_PATH)/lib/version.txt),)
    WARNING_MESSAGE = 'ARDUINO 1.5.x IS REPLACED BY ARDUINO 1.6.x'
endif


# Arduino 1.6.x AVR specifics
# ----------------------------------
#
PLATFORM         := Arduino
BUILD_CORE       := avr
PLATFORM_TAG      = ARDUINO=$(ARDUINO_RELEASE) ARDUINO_ARCH_AVR EMBEDXCODE=$(RELEASE_NOW) ARDUINO_$(ARDUINO_NAME)
APPLICATION_PATH := $(ARDUINO_PATH)

APP_TOOLS_PATH   := $(APPLICATION_PATH)/hardware/tools/avr/bin
CORE_LIB_PATH    := $(APPLICATION_PATH)/hardware/arduino/avr/cores/arduino
APP_LIB_PATH     := $(APPLICATION_PATH)/libraries
BOARDS_TXT       := $(APPLICATION_PATH)/hardware/arduino/avr/boards.txt
ARDUINO_NAME      =  $(call PARSE_BOARD,$(BOARD_TAG),build.board)

ifneq ($(findstring LITTLEROBOTFRIENDS,$(GCC_PREPROCESSOR_DEFINITIONS)),)
    BOARDS_TXT   := $(LITTLEROBOTFRIENDS_PATH)/boards.txt
endif

# Sketchbook/Libraries path
# wildcard required for ~ management
# ?ibraries required for libraries and Libraries
#
ifeq ($(USER_PATH)/Library/Arduino15/preferences.txt,)
    $(error Error: run Arduino once and define the sketchbook path)
endif

ifeq ($(wildcard $(SKETCHBOOK_DIR)),)
    SKETCHBOOK_DIR = $(shell grep sketchbook.path $(USER_PATH)/Library/Arduino15/preferences.txt | cut -d = -f 2)
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


# Complicated menu system for Arduino 1.5
# Another example of Arduino's quick and dirty job
#
BOARD_NAME       := $(call PARSE_BOARD,$(BOARD_TAG),name)
ifeq ($(BOARD_NAME),)
    BOARD_NAME     := $(call PARSE_BOARD,$(BOARD_TAG1),name)
    ifeq ($(BOARD_NAME),)
        BOARD_NAME   := $(call PARSE_BOARD,$(BOARD_TAG2),name)
    endif
endif

ifeq ($(MCU),)
    MCU         := $(call PARSE_BOARD,$(BOARD_TAG),build.mcu)
    ifeq ($(MCU),)
        MCU       := $(call PARSE_BOARD,$(BOARD_TAG1),build.mcu)
        ifeq ($(MCU),)
            MCU     := $(call PARSE_BOARD,$(BOARD_TAG2),build.mcu)
        endif
    endif
endif

ifeq ($(F_CPU),)
    F_CPU       := $(call PARSE_BOARD,$(BOARD_TAG),build.f_cpu)
    ifeq ($(F_CPU),)
        F_CPU     := $(call PARSE_BOARD,$(BOARD_TAG1),build.f_cpu)
        ifeq ($(F_CPU),)
            F_CPU   := $(call PARSE_BOARD,$(BOARD_TAG2),build.f_cpu)
        endif
    endif
endif

ifeq ($(MAX_FLASH_SIZE),)
    MAX_FLASH_SIZE       := $(call PARSE_BOARD,$(BOARD_TAG),upload.maximum_size)
    ifeq ($(MAX_FLASH_SIZE),)
        MAX_FLASH_SIZE     := $(call PARSE_BOARD,$(BOARD_TAG1),upload.maximum_size)
        ifeq ($(MAX_FLASH_SIZE),)
            MAX_FLASH_SIZE   := $(call PARSE_BOARD,$(BOARD_TAG2),upload.maximum_size)
        endif
    endif
endif

ifeq ($(AVRDUDE_BAUDRATE),)
    AVRDUDE_BAUDRATE        := $(call PARSE_BOARD,$(BOARD_TAG),upload.speed)
    ifeq ($(AVRDUDE_BAUDRATE),)
        AVRDUDE_BAUDRATE      := $(call PARSE_BOARD,$(BOARD_TAG1),upload.speed)
        ifeq ($(AVRDUDE_BAUDRATE),)
            AVRDUDE_BAUDRATE    := $(call PARSE_BOARD,$(BOARD_TAG2),upload.speed)
        endif
    endif
endif

ifeq ($(AVRDUDE_PROGRAMMER),)
    AVRDUDE_PROGRAMMER      := $(call PARSE_BOARD,$(BOARD_TAG),upload.protocol)
    ifeq ($(AVRDUDE_PROGRAMMER),)
        AVRDUDE_PROGRAMMER    := $(call PARSE_BOARD,$(BOARD_TAG1),upload.protocol)
        ifeq ($(AVRDUDE_PROGRAMMER),)
            AVRDUDE_PROGRAMMER  := $(call PARSE_BOARD,$(BOARD_TAG2),upload.protocol)
        endif
    endif
endif

#ifeq ($(BOARD_TAG2),)
#  MCU   = $(call PARSE_BOARD,$(BOARD_TAG),build.mcu)
#  F_CPU = $(call PARSE_BOARD,$(BOARD_TAG),build.f_cpu)
#else
#  MCU   = $(call PARSE_BOARD,$(BOARD_TAG2),build.mcu)
#  F_CPU = $(call PARSE_BOARD,$(BOARD_TAG2),build.f_cpu)
#endif

# Specific AVRDUDE location and options
#
AVRDUDE_COM_OPTS  = -D -p$(MCU) -C$(AVRDUDE_CONF)

ifneq ($(BOARD_TAG1),)
#BOARD        = $(call PARSE_BOARD,$(BOARD_TAG1),board)
#LDSCRIPT    = $(call PARSE_BOARD,$(BOARD_TAG1),ldscript)
    a1501         = $(call PARSE_BOARD,$(BOARD_TAG1),build.variant)
    VARIANT      = $(patsubst arduino:%,%,$(a1501))
    VARIANT_PATH = $(APPLICATION_PATH)/hardware/arduino/avr/variants/$(VARIANT)
else
#BOARD        = $(call PARSE_BOARD,$(BOARD_TAG),board)
#LDSCRIPT    = $(call PARSE_BOARD,$(BOARD_TAG),ldscript)
    a1501         = $(call PARSE_BOARD,$(BOARD_TAG),build.variant)
    VARIANT      = $(patsubst arduino:%,%,$(a1501))
    VARIANT_PATH = $(APPLICATION_PATH)/hardware/arduino/avr/variants/$(VARIANT)
endif

# Two locations for Arduino libraries
#
BUILD_APP_LIB_PATH  = $(APPLICATION_PATH)/hardware/arduino/$(BUILD_CORE)/libraries

ifndef APP_LIBS_LIST
    a1501             = $(realpath $(sort $(dir $(wildcard $(APP_LIB_PATH)/*/*.h $(APP_LIB_PATH)/*/*/*.h $(APP_LIB_PATH)/*/*/*/*.h)))) # */
    APP_LIBS_LIST  = $(subst $(APP_LIB_PATH)/,,$(filter-out $(EXCLUDE_LIST),$(a1501)))

    a1502             = $(realpath $(sort $(dir $(wildcard $(BUILD_APP_LIB_PATH)/*/*.h $(BUILD_APP_LIB_PATH)/*/*/*.h $(BUILD_APP_LIB_PATH)/*/*/*/*.h)))) # */
    BUILD_APP_LIBS_LIST = $(subst $(BUILD_APP_LIB_PATH)/,,$(filter-out $(EXCLUDE_LIST),$(a1502)))
else
    a1502             = $(realpath $(sort $(dir $(wildcard $(BUILD_APP_LIB_PATH)/*/*.h $(BUILD_APP_LIB_PATH)/*/*/*.h $(BUILD_APP_LIB_PATH)/*/*/*/*.h)))) # */
    BUILD_APP_LIBS_LIST = $(subst $(BUILD_APP_LIB_PATH)/,,$(filter-out $(EXCLUDE_LIST),$(a1502)))
endif


# Arduino 1.5.x nightmare with src and arch/sam or arch/avr
# Another example of Arduino's quick and dirty job
#
ifneq ($(APP_LIBS_LIST),0)
    a1503              = $(patsubst %,$(APP_LIB_PATH)/%/src,$(APP_LIBS_LIST))
    a1503             += $(patsubst %,$(APP_LIB_PATH)/%/arch/$(BUILD_CORE),$(APP_LIBS_LIST))
    APP_LIBS        = $(realpath $(sort $(dir $(foreach dir,$(a1503),$(wildcard $(dir)/*.h $(dir)/*/*.h $(dir)/*/*/*.h))))) # */

    APP_LIB_CPP_SRC = $(realpath $(sort $(foreach dir,$(APP_LIBS),$(wildcard $(dir)/*.cpp $(dir)/*/*.cpp $(dir)/*/*/*.cpp))))
    APP_LIB_C_SRC   = $(realpath $(sort $(foreach dir,$(APP_LIBS),$(wildcard $(dir)/*.c $(dir)/*/*.c $(dir)/*/*/*.c))))

    APP_LIB_OBJS    = $(patsubst $(APP_LIB_PATH)/%.cpp,$(OBJDIR)/libs/%.cpp.o,$(APP_LIB_CPP_SRC))
    APP_LIB_OBJS   += $(patsubst $(APP_LIB_PATH)/%.c,$(OBJDIR)/libs/%.c.o,$(APP_LIB_C_SRC))

    BUILD_APP_LIBS        = $(patsubst %,$(BUILD_APP_LIB_PATH)/%,$(BUILD_APP_LIBS_LIST))

    BUILD_APP_LIB_CPP_SRC = $(wildcard $(patsubst %,%/*.cpp,$(BUILD_APP_LIBS))) # */
    BUILD_APP_LIB_C_SRC   = $(wildcard $(patsubst %,%/*.c,$(BUILD_APP_LIBS))) # */

    BUILD_APP_LIB_OBJS    = $(patsubst $(BUILD_APP_LIB_PATH)/%.cpp,$(OBJDIR)/libs/%.cpp.o,$(BUILD_APP_LIB_CPP_SRC))
    BUILD_APP_LIB_OBJS   += $(patsubst $(BUILD_APP_LIB_PATH)/%.c,$(OBJDIR)/libs/%.c.o,$(BUILD_APP_LIB_C_SRC))
endif


MCU_FLAG_NAME  = mmcu
EXTRA_LDFLAGS  = 
EXTRA_CPPFLAGS = -I$(VARIANT_PATH) $(addprefix -D, $(PLATFORM_TAG)) -fno-threadsafe-statics -MMD


# Arduino Leonardo USB PID VID
#
USB_VID   := $(call PARSE_BOARD,$(BOARD_TAG),build.vid)
USB_PID   := $(call PARSE_BOARD,$(BOARD_TAG),build.pid)

ifneq ($(USB_PID),)
ifneq ($(USB_VID),)
    USB_FLAGS  = -DUSB_VID=$(USB_VID)
    USB_FLAGS += -DUSB_PID=$(USB_PID) 
endif
endif

# Arduino Leonardo serial 1200 reset
#
USB_TOUCH := $(call PARSE_BOARD,$(BOARD_TAG),upload.protocol)

ifeq ($(USB_TOUCH),avr109)
    USB_RESET  = python $(UTILITIES_PATH)/reset_1200.py
endif
