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
# Last update: Mar 20, 2015 release 270

# ARDUINO 1.5.X IS STILL IN BETA, UNSTABLE AND PRONE TO BUGS
WARNING_MESSAGE = 'ARDUINO 1.5.X IS STILL IN BETA, UNSTABLE AND PRONE TO BUGS'


# Arduino 1.5.x SAM specifics
# ----------------------------------
#
PLATFORM         := RedBearLab
BUILD_CORE       := RBL_nRF51822
PLATFORM_TAG      = ARDUINO=$(ARDUINO_RELEASE) EMBEDXCODE=$(RELEASE_NOW) $(GCC_PREPROCESSOR_DEFINITIONS)
APPLICATION_PATH := $(REDBEARLAB_PATH)

# New GCC for ARM tool-suite
#
ifeq ($(wildcard $(APPLICATION_PATH)/hardware/tools/g++_arm_none_eabi),)
    APP_TOOLS_PATH   := $(APPLICATION_PATH)/hardware/tools/gcc-arm-none-eabi-4.8.3-2014q1/bin
else
    APP_TOOLS_PATH   := $(APPLICATION_PATH)/hardware/tools/g++_arm_none_eabi/bin
endif

CORE_LIB_PATH    := $(APPLICATION_PATH)/hardware/arduino/RBL_nRF51822/cores/arduino
APP_LIB_PATH     := $(APPLICATION_PATH)/libraries
BOARDS_TXT       := $(APPLICATION_PATH)/hardware/arduino/RBL_nRF51822/boards.txt

# 
# Uploader bossac 
# Tested by Mike Roberts 
#
#UPLOADER          = bossac
#BOSSAC_PATH       = $(APPLICATION_PATH)/hardware/tools
#BOSSAC            = $(BOSSAC_PATH)/bossac
#BOSSAC_PORT       = $(subst /dev/,,$(AVRDUDE_PORT))
#BOSSAC_OPTS       = --port=$(BOSSAC_PORT) -U false -e -w -v -b

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
CC      = $(APP_TOOLS_PATH)/arm-none-eabi-gcc
CXX     = $(APP_TOOLS_PATH)/arm-none-eabi-g++
AR      = $(APP_TOOLS_PATH)/arm-none-eabi-ar
OBJDUMP = $(APP_TOOLS_PATH)/arm-none-eabi-objdump
OBJCOPY = $(APP_TOOLS_PATH)/arm-none-eabi-objcopy
SIZE    = $(APP_TOOLS_PATH)/arm-none-eabi-size
NM      = $(APP_TOOLS_PATH)/arm-none-eabi-nm

# Specific AVRDUDE location and options
#
AVRDUDE_COM_OPTS  = -D -p$(MCU) -C$(AVRDUDE_CONF)

BOARD    = $(call PARSE_BOARD,$(BOARD_TAG),board)
LDSCRIPT = $(call PARSE_BOARD,$(BOARD_TAG),build.ldscript)
VARIANT  = $(call PARSE_BOARD,$(BOARD_TAG),build.variant)
VARIANT_PATH = $(APPLICATION_PATH)/hardware/arduino/RBL_nRF51822/variants/$(VARIANT)
VARIANT_CPP_SRCS  = $(wildcard $(VARIANT_PATH)/*.cpp) # */  $(VARIANT_PATH)/*/*.cpp #*/
VARIANT_OBJ_FILES = $(VARIANT_CPP_SRCS:.cpp=.cpp.o)
VARIANT_OBJS      = $(patsubst $(VARIANT_PATH)/%,$(OBJDIR)/%,$(VARIANT_OBJ_FILES))

SYSTEM_LIB  = $(call PARSE_BOARD,$(BOARD_TAG),build.variant_system_lib)
SYSTEM_PATH = $(VARIANT_PATH)
SYSTEM_OBJS = $(SYSTEM_PATH)/$(SYSTEM_LIB)


# Two locations for Arduino libraries
#
BUILD_APP_LIB_PATH  = $(APPLICATION_PATH)/hardware/arduino/$(BUILD_CORE)/libraries

ifndef APP_LIBS_LIST
    r01             = $(realpath $(sort $(dir $(wildcard $(APP_LIB_PATH)/*/*.h $(APP_LIB_PATH)/*/*/*.h $(APP_LIB_PATH)/*/*/*/*.h)))) # */
    APP_LIBS_LIST  = $(subst $(APP_LIB_PATH)/,,$(filter-out $(EXCLUDE_LIST),$(r01)))

    r02             = $(realpath $(sort $(dir $(wildcard $(BUILD_APP_LIB_PATH)/*/*.h $(BUILD_APP_LIB_PATH)/*/*/*.h $(BUILD_APP_LIB_PATH)/*/*/*/*.h)))) # */
    BUILD_APP_LIBS_LIST = $(subst $(BUILD_APP_LIB_PATH)/,,$(filter-out $(EXCLUDE_LIST),$(r02)))
else
    r02             = $(realpath $(sort $(dir $(wildcard $(BUILD_APP_LIB_PATH)/*/*.h $(BUILD_APP_LIB_PATH)/*/*/*.h $(BUILD_APP_LIB_PATH)/*/*/*/*.h)))) # */
    BUILD_APP_LIBS_LIST = $(subst $(BUILD_APP_LIB_PATH)/,,$(filter-out $(EXCLUDE_LIST),$(r02)))
endif


# Arduino 1.5.x nightmare with src and arch/sam or arch/avr
# Another example of Arduino's quick and dirty job
#
ifneq ($(APP_LIBS_LIST),0)
    r03              = $(patsubst %,$(APP_LIB_PATH)/%/src,$(APP_LIBS_LIST))
    r03             += $(patsubst %,$(APP_LIB_PATH)/%/arch/$(BUILD_CORE),$(APP_LIBS_LIST))
    APP_LIBS        = $(realpath $(sort $(dir $(foreach dir,$(r03),$(wildcard $(dir)/*.h $(dir)/*/*.h $(dir)/*/*/*.h))))) # */

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

# RedBearLab long list of paths to be included
#
INCLUDE_PATH    = -I$(APPLICATION_PATH)/hardware/arduino/RBL_nRF51822/cores/arduino
INCLUDE_PATH   += -I$(VARIANT_PATH)
#INCLUDE_PATH   += -I$(APPLICATION_PATH)/hardware/arduino/RBL_nRF51822/system/CMSIS/CMSIS/Include

r05             = $(call PARSE_BOARD,$(BOARD_TAG),build.variant_system_include)
r06             = $(subst {build.system.path},$(APPLICATION_PATH)/hardware/arduino/RBL_nRF51822/system,$(r05))
INCLUDE_PATH   += $(subst {runtime.ide.path},$(APPLICATION_PATH),$(r06))

#INCLUDE_PATH   += -I$(APPLICATION_PATH)/hardware/arduino/RBL_nRF51822/nrf51822_SDK/Include
#INCLUDE_PATH   += -I$(APPLICATION_PATH)/hardware/arduino/RBL_nRF51822/nrf51822_SDK/Include/app_common
#INCLUDE_PATH   += -I$(APPLICATION_PATH)/hardware/arduino/RBL_nRF51822/nrf51822_SDK/Include/ble
#INCLUDE_PATH   += -I$(APPLICATION_PATH)/hardware/arduino/RBL_nRF51822/nrf51822_SDK/Include/ble/ble_services
#INCLUDE_PATH   += -I$(APPLICATION_PATH)/hardware/arduino/RBL_nRF51822/nrf51822_SDK/Include/ble/rpc
#INCLUDE_PATH   += -I$(APPLICATION_PATH)/hardware/arduino/RBL_nRF51822/nrf51822_SDK/Include/gcc
#INCLUDE_PATH   += -I$(APPLICATION_PATH)/hardware/arduino/RBL_nRF51822/nrf51822_SDK/Include/s110
#INCLUDE_PATH   += -I$(APPLICATION_PATH)/hardware/arduino/RBL_nRF51822/nrf51822_SDK/Include/sd_common
#INCLUDE_PATH   += -I$(APPLICATION_PATH)/hardware/arduino/RBL_nRF51822/nrf51822_SDK/Include/sdk


MCU_FLAG_NAME   = mcpu
EXTRA_LDFLAGS   = -T$(VARIANT_PATH)/$(LDSCRIPT) -Wl,-Map,Builds/embeddedcomputing.map $(VARIANT_OBJS)
EXTRA_LDFLAGS  += -lgcc -mthumb -Wl,--cref -Wl,--check-sections -Wl,--gc-sections -Wl,--entry=Reset_Handler 
EXTRA_LDFLAGS  += -Wl,--unresolved-symbols=report-all -Wl,--warn-common -Wl,--warn-section-align 
EXTRA_LDFLAGS  += -Wl,--warn-unresolved-symbols --specs=nano.specs

LDFLAGS         = -$(MCU_FLAG_NAME)=$(MCU) -lm -Wl,--gc-sections $(OPTIMISATION) $(EXTRA_LDFLAGS)

EXTRA_CPPFLAGS  = $(addprefix -D, $(PLATFORM_TAG))
EXTRA_CPPFLAGS += -DBLE_STACK_SUPPORT_REQD -DDEBUG_NRF_USER -DBOARD_PCA10001
EXTRA_CPPFLAGS +=  -fno-rtti -mthumb -nostdlib --param max-inline-insns-single=500 -Dprintf=iprintf $(INCLUDE_PATH)

#LIBRARY_A        = $(APPLICATION_PATH)/hardware/arduino/RBL_nRF51822/nrf51822_SDK/nrf_sdk.a
FIRST_O_IN_A    = $(OBJDIR)/startup_nrf51822.c.o

#OBJCOPYFLAGS  = -v -Obinary
OBJCOPYFLAGS  = -v -Obinary
TARGET_HEXBIN = $(TARGET_BIN)

# Serial 1200 reset
#
USB_TOUCH := $(call PARSE_BOARD,$(BOARD_TAG),upload.use_1200bps_touch)
ifeq ($(USB_TOUCH),true)
    USB_RESET  = python $(UTILITIES_PATH)/reset_1200.py
endif







