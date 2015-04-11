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



# Energia LaunchPad Stellaris and Tiva C specifics
# ----------------------------------
#
APPLICATION_PATH := $(ENERGIA_PATH)
ENERGIA_RELEASE  := $(shell tail -c2 $(APPLICATION_PATH)/lib/version.txt)
ARDUINO_RELEASE  := $(shell head -c4 $(APPLICATION_PATH)/lib/version.txt | tail -c3)

ifeq ($(shell if [[ '$(ENERGIA_RELEASE)' -ge '14' ]] ; then echo 1 ; else echo 0 ; fi ),0)
    $(error Energia release 14 required.)
endif

PLATFORM         := Energia
BUILD_CORE       := cc3200
PLATFORM_TAG      = ENERGIA=$(ENERGIA_RELEASE) ARDUINO=$(ARDUINO_RELEASE) EMBEDXCODE=$(RELEASE_NOW) $(filter __%__ ,$(GCC_PREPROCESSOR_DEFINITIONS))

UPLOADER          = cc3200serial
UPLOADER_PATH = $(APPLICATION_PATH)/hardware/tools
ifneq ($(wildcard $(UPLOADER_PATH)/lm4f/bin/serial),)
    UPLOADER_EXEC      = $(UPLOADER_PATH)/lm4f/bin/serial
else
    UPLOADER_EXEC      = $(UPLOADER_PATH)/lm4f/bin/cc3200prog
endif
UPLOADER_OPTS =

# StellarPad requires a specific command
#
UPLOADER_COMMAND = prog

APP_TOOLS_PATH   := $(APPLICATION_PATH)/hardware/tools/lm4f/bin
CORE_LIB_PATH    := $(APPLICATION_PATH)/hardware/cc3200/cores/cc3200
APP_LIB_PATH     := $(APPLICATION_PATH)/hardware/cc3200/libraries
BOARDS_TXT       := $(APPLICATION_PATH)/hardware/cc3200/boards.txt

BUILD_CORE_LIB_PATH  = $(APPLICATION_PATH)/hardware/cc3200/cores/cc3200/driverlib
BUILD_CORE_LIBS_LIST = $(subst .h,,$(subst $(BUILD_CORE_LIB_PATH)/,,$(wildcard $(BUILD_CORE_LIB_PATH)/*.h))) # */

BUILD_CORE_C_SRCS    = $(wildcard $(BUILD_CORE_LIB_PATH)/*.c) # */

BUILD_CORE_CPP_SRCS = $(filter-out %program.cpp %main.cpp,$(wildcard $(BUILD_CORE_LIB_PATH)/*.cpp)) # */

BUILD_CORE_OBJ_FILES  = $(BUILD_CORE_C_SRCS:.c=.c.o) $(BUILD_CORE_CPP_SRCS:.cpp=.cpp.o)
BUILD_CORE_OBJS       = $(patsubst $(BUILD_CORE_LIB_PATH)/%,$(OBJDIR)/%,$(BUILD_CORE_OBJ_FILES))

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
CC      = $(APP_TOOLS_PATH)/arm-none-eabi-gcc
CXX     = $(APP_TOOLS_PATH)/arm-none-eabi-g++
AR      = $(APP_TOOLS_PATH)/arm-none-eabi-ar
OBJDUMP = $(APP_TOOLS_PATH)/arm-none-eabi-objdump
OBJCOPY = $(APP_TOOLS_PATH)/arm-none-eabi-objcopy
SIZE    = $(APP_TOOLS_PATH)/arm-none-eabi-size
NM      = $(APP_TOOLS_PATH)/arm-none-eabi-nm


BOARD    = $(call PARSE_BOARD,$(BOARD_TAG),board)
LDSCRIPT = $(call PARSE_BOARD,$(BOARD_TAG),ldscript)
VARIANT  = $(call PARSE_BOARD,$(BOARD_TAG),build.variant)
VARIANT_PATH = $(APPLICATION_PATH)/hardware/cc3200/variants/$(VARIANT)

OPTIMISATION   = -Os

MCU_FLAG_NAME   = mcpu
EXTRA_LDFLAGS   = -nostartfiles -nostdlib -T$(CORE_LIB_PATH)/$(LDSCRIPT) -Wl,--gc-sections
EXTRA_LDFLAGS  += -Wl,--entry=ResetISR -mthumb

EXTRA_CPPFLAGS  = $(addprefix -D, $(PLATFORM_TAG)) -I$(VARIANT_PATH)
EXTRA_CPPFLAGS += -fno-exceptions -fno-rtti -mthumb $(OPTIMISATION) -MMD

OBJCOPYFLAGS  = -Obinary # -v
TARGET_HEXBIN = $(TARGET_BIN)
