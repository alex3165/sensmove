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
# Last update: Mar 23, 2015 release 271



# panStamp NRG specifics
# ----------------------------------
#
PLATFORM         := panStamp
PLATFORM_TAG      = ARDUINO=10600 ARDUINO_PANSTAMP_NRG ARDUINO_ARCH_MSP430 EMBEDXCODE=$(RELEASE_NOW) PANSTAMP_NRG
APPLICATION_PATH := $(PANSTAMP_PATH)

BUILD_CORE       = avr
BOARDS_TXT      := $(APPLICATION_PATH)/hardware/panstamp/msp430/boards.txt
BUILD_CORE       = $(call PARSE_BOARD,$(BOARD_TAG),build.core)

# Uploader
#
UPLOADER        = BsLoader.jar
UPLOADER_PATH   = $(APPLICATION_PATH)/app/BsLoader/dist
UPLOADER_EXEC   = java -jar $(UPLOADER_PATH)/BsLoader.jar
UPLOADER_OPTS   = --verif-off --verbose-off
UPLOADER_RESET  =
RESET_MESSAGE   = 1

APP_TOOLS_PATH   := $(APPLICATION_PATH)/hardware/tools/msp430/bin
CORE_LIB_PATH    := $(APPLICATION_PATH)/hardware/panstamp/msp430/cores/panstamp
APP_LIB_PATH     := $(APPLICATION_PATH)/libraries

BUILD_CORE_LIB_PATH  = $(APPLICATION_PATH)/hardware/panstamp/msp430/cores/panstamp
BUILD_CORE_LIBS_LIST = $(subst .h,,$(subst $(BUILD_CORE_LIB_PATH)/,,$(wildcard $(BUILD_CORE_LIB_PATH)/*.h))) # */
BUILD_CORE_C_SRCS    = $(wildcard $(BUILD_CORE_LIB_PATH)/*.c) # */

BUILD_CORE_CPP_SRCS  = $(filter-out %program.cpp %main.cpp,$(wildcard $(BUILD_CORE_LIB_PATH)/*.cpp)) # */

BUILD_CORE_OBJ_FILES = $(BUILD_CORE_C_SRCS:.c=.c.o) $(BUILD_CORE_CPP_SRCS:.cpp=.cpp.o)
BUILD_CORE_OBJS      = $(patsubst $(BUILD_CORE_LIB_PATH)/%,$(OBJDIR)/%,$(BUILD_CORE_OBJ_FILES))


# Two locations for panStamp libraries
#
APP_LIB_PATH        := $(APPLICATION_PATH)/libraries
BUILD_APP_LIB_PATH  := $(APPLICATION_PATH)/hardware/panstamp/msp430/libraries

ifndef APP_LIBS_LIST
    ps01             = $(realpath $(sort $(dir $(wildcard $(APP_LIB_PATH)/*/*.h $(APP_LIB_PATH)/*/*/*.h $(APP_LIB_PATH)/*/*/*/*.h)))) # */
    APP_LIBS_LIST    = $(subst $(APP_LIB_PATH)/,,$(filter-out $(EXCLUDE_LIST),$(ps01)))

    ps02             = $(realpath $(sort $(dir $(wildcard $(BUILD_APP_LIB_PATH)/*/*.h $(BUILD_APP_LIB_PATH)/*/*/*.h $(BUILD_APP_LIB_PATH)/*/*/*/*.h)))) # */
    BUILD_APP_LIBS_LIST = $(subst $(BUILD_APP_LIB_PATH)/,,$(filter-out $(EXCLUDE_LIST),$(ps02)))
else
    ps01              = $(patsubst %,$(BUILD_APP_LIB_PATH)/%,$(APP_LIBS_LIST))
    ps02             += $(realpath $(sort $(dir $(foreach dir,$(ps01),$(wildcard $(dir)/*.h $(dir)/*/*.h $(dir)/*/*/*.h))))) # */
    BUILD_APP_LIBS_LIST = $(subst $(BUILD_APP_LIB_PATH)/,,$(filter-out $(EXCLUDE_LIST),$(ps02)))
endif

ifneq ($(APP_LIBS_LIST),0)
    ps04              = $(patsubst %,$(APP_LIB_PATH)/%,$(APP_LIBS_LIST))
    ps04             += $(patsubst %,$(APP_LIB_PATH)/%/$(BUILD_CORE),$(APP_LIBS_LIST))
    APP_LIBS        = $(realpath $(sort $(dir $(foreach dir,$(ps04),$(wildcard $(dir)/*.h $(dir)/*/*.h $(dir)/*/*/*.h))))) # */

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


# Sketchbook/Libraries path
# wildcard required for ~ management
# ?ibraries required for libraries and Libraries
#
ifeq ($(USER_PATH)/Library/Arduino/preferences.txt,)
    $(error Error: run Arduino or panStamp once and define the sketchbook path)
endif

ifeq ($(wildcard $(SKETCHBOOK_DIR)),)
    SKETCHBOOK_DIR = $(shell grep sketchbook.path $(wildcard ~/Library/Arduino/preferences.txt) | cut -d = -f 2)
endif

ifeq ($(wildcard $(SKETCHBOOK_DIR)),)
    $(error Error: sketchbook path not found)
endif

USER_LIB_PATH   = $(wildcard $(SKETCHBOOK_DIR)/?ibraries)

VARIANT         = $(call PARSE_BOARD,$(BOARD_TAG),build.variant)
VARIANT_PATH    = $(APPLICATION_PATH)/hardware/panstamp/msp430/variants/$(VARIANT)
LDSCRIPT_PATH   = $(APPLICATION_PATH)/hardware/panstamp/msp430/ldscript
LDSCRIPT        = $(call PARSE_BOARD,$(BOARD_TAG),ldscript).x

# Rules for making a c++ file from the main sketch (.pde)
#
PDEHEADER      = \\\#include \"WProgram.h\"  


# Tool-chain names
#
CC      = $(APP_TOOLS_PATH)/msp430-gcc
CXX     = $(APP_TOOLS_PATH)/msp430-g++
AR      = $(APP_TOOLS_PATH)/msp430-ar
OBJDUMP = $(APP_TOOLS_PATH)/msp430-objdump
OBJCOPY = $(APP_TOOLS_PATH)/msp430-objcopy
SIZE    = $(APP_TOOLS_PATH)/msp430-size
NM      = $(APP_TOOLS_PATH)/msp430-nm


MCU_FLAG_NAME   = mmcu
MCU             = $(call PARSE_BOARD,$(BOARD_TAG),build.mcu)
F_CPU           = $(call PARSE_BOARD,$(BOARD_TAG),build.f_cpu)
OPTIMISATION    = -Os


# Flags for gcc, g++ and linker
# ----------------------------------
#
# Common CPPFLAGS for gcc, g++, assembler and linker
#
CPPFLAGS     = -g $(OPTIMISATION) $(WARNING_FLAGS)  # -w
CPPFLAGS    += -ffunction-sections -fdata-sections -MMD
CPPFLAGS    += -$(MCU_FLAG_NAME)=$(MCU) -DF_CPU=$(F_CPU)
#CPPFLAGS    += $(call PARSE_BOARD,$(BOARD_TAG),build.board)
#CPPFLAGS    += $(call PARSE_BOARD,$(BOARD_TAG),build.flags.common)
CPPFLAGS    += $(addprefix -D, $(PLATFORM_TAG))
CPPFLAGS    += -I$(CORE_LIB_PATH) -I$(VARIANT_PATH) -I$(OBJDIR)

# Specific CFLAGS for gcc only
# gcc uses CPPFLAGS and CFLAGS
#
CFLAGS       = -Wno-unused-but-set-variable

# Specific CXXFLAGS for g++ only
# g++ uses CPPFLAGS and CXXFLAGS
#
CXXFLAGS     = -std=gnu++98 -fwrapv -fomit-frame-pointer -ggdb3 -fno-rtti -Wno-unused

# Specific ASFLAGS for gcc assembler only
# gcc assembler uses CPPFLAGS and ASFLAGS
#
ASFLAGS      = -x assembler-with-cpp

# Specific LDFLAGS for linker only
# linker uses CPPFLAGS and LDFLAGS
#
LDFLAGS      = -w $(OPTIMISATION) -Wl,--gc-sections,-u,main,-L $(LDSCRIPT_PATH)
LDFLAGS     += -$(MCU_FLAG_NAME)=$(MCU) -DF_CPU=$(F_CPU)
LDFLAGS     += -T $(LDSCRIPT)
#LDFLAGS     += $(OPTIMISATION) $(call PARSE_BOARD,$(BOARD_TAG),build.flags.ldspecs)
#LDFLAGS     += $(call PARSE_BOARD,$(BOARD_TAG),build.flags.libs) --verbose


OBJCOPYFLAGS  = -O ihex -R .eeprom
TARGET_HEXBIN = $(TARGET_HEX)
#-O ihex -j .eeprom --set-section-flags=.eeprom=alloc,load --no-change-warnings --change-section-lma .eeprom=0
TARGET_EEP    = $(OBJDIR)/$(TARGET).eep

