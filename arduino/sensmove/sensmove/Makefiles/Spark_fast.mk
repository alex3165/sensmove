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

INFO_MESSAGE = 'Using pre-compiled Spark framework'

# mbed specifics
# ----------------------------------
#
CORE_LIB_PATH   = $(APPLICATION_PATH)/build


# Special files
#
SPARK_A     = $(CORE_LIB_PATH)/$(LEVEL0)/$(TOOLCHAIN)/lib_eXspark.a
#STARTUP_O   = $(APPLICATION_PATH)/build/$(STARTUP).o


# More options
#
MORE_TARGET_EXCLUDE    = $(filter-out $(MORE_TARGET_INCLUDE),TARGET_M0 TARGET_M0P TARGET_M3 TARGET_M4)
MORE_TOOLCHAIN_EXCLUDE = $(filter-out $(MORE_TOOLCHAIN_INCLUDE),TOOLCHAIN_ARM TOOLCHAIN_GCC)

ifneq ($(EXCLUDE_NAMES),)
    EXCLUDE_LIST   += $(addprefix %,$(EXCLUDE_NAMES).h)
    EXCLUDE_LIST   += $(addprefix %,$(EXCLUDE_NAMES).c)
    EXCLUDE_LIST   += $(addprefix %,$(EXCLUDE_NAMES).cpp)
#    EXCLUDE_LIST   += $(addprefix %,$(EXCLUDE_NAMES).s)
endif

D_OPTIONS   = STM32_DEVICE SPARK_PRODUCT_ID=65535 SPARK_PLATFORM PRODUCT_FIRMWARE_VERSION=65535
D_OPTIONS  += RELEASE_BUILD INCLUDE_PLATFORM=1 SPARK_PLATFORM_NET=$(LEVEL3)
D_OPTIONS  += USE_STDPERIPH_DRIVER DFU_BUILD_ENABLE


# CORE files
#
BUILD_CORE_LIB_PATH = $(APPLICATION_PATH)/firmware

BUILD_CORE_AS_SRCS  = $(BUILD_CORE_LIB_PATH)/build/arm/startup/$(STARTUP)
STARTUP_O          := $(patsubst $(BUILD_CORE_LIB_PATH)/%,$(OBJDIR)/%,$(CORE_AS_SRCS:.S=.S.o))


# APPlication files
#
APP_LIB_PATH            = $(BUILD_CORE_LIB_PATH)/wiring
u201                   += $(BUILD_CORE_LIB_PATH)/wiring/inc
u201                   += $(BUILD_CORE_LIB_PATH)/wiring/src

APP_LIB_PATH          = $(APPLICATION_PATH)/build

ifneq ($(APP_LIBS_LIST),0)
    $(error The option APP_LIBS_LIST is not allowed for Spark. All libraries are included.)
    APP_LIBS_LIST :=
endif


# Special files
#
CORE_AS_SRCS            = $(CORE_LIB_PATH)/build/arm/startup/$(STARTUP)
STARTUP_O              := $(patsubst $(CORE_LIB_PATH)/%,$(OBJDIR)/%,$(CORE_AS_SRCS:.S=.S.o))
FIRST_O_IN_A           := $(STARTUP_O)


# USER files
# Sketchbook/Libraries path
# wildcard required for ~ management
# ?ibraries required for libraries and Libraries
#
ifeq ($(SPARK_PATH)/preferences.txt,)
    $(error Error: define sketchbook.path in preferences.txt first)
endif

ifeq ($(wildcard $(SKETCHBOOK_DIR)),)
    SKETCHBOOK_DIR = $(shell grep sketchbook.path $(SPARK_PATH)/preferences.txt | cut -d = -f 2-)
endif

ifeq ($(wildcard $(SKETCHBOOK_DIR)),)
    $(error Error: sketchbook path not found ($(SKETCHBOOK_DIR)))
endif

USER_LIB_PATH  = $(wildcard $(SKETCHBOOK_DIR)/?ibraries)


# VARIANT files
#


# SYSTEM files
#


# Include paths
#
INCLUDE_PATH  = $(BUILD_CORE_LIB_PATH)/communication/src
INCLUDE_PATH += $(BUILD_CORE_LIB_PATH)/communication/lib/tropicssl/include
INCLUDE_PATH += $(BUILD_CORE_LIB_PATH)/hal/inc
INCLUDE_PATH += $(BUILD_CORE_LIB_PATH)/hal/shared
INCLUDE_PATH += $(BUILD_CORE_LIB_PATH)/hal/src/$(LEVEL1)
INCLUDE_PATH += $(BUILD_CORE_LIB_PATH)/main/inc
INCLUDE_PATH += $(BUILD_CORE_LIB_PATH)/main/libraries/Serial2
INCLUDE_PATH += $(BUILD_CORE_LIB_PATH)/main/libraries/Ymodem
INCLUDE_PATH += $(BUILD_CORE_LIB_PATH)/platform/MCU/$(LEVEL2)/CMSIS/Device/ST/Include
INCLUDE_PATH += $(BUILD_CORE_LIB_PATH)/platform/MCU/$(LEVEL2)/CMSIS/Include
INCLUDE_PATH += $(BUILD_CORE_LIB_PATH)/platform/MCU/$(LEVEL2)/SPARK_Firmware_Driver/inc
INCLUDE_PATH += $(BUILD_CORE_LIB_PATH)/platform/MCU/$(LEVEL2)/STM32_StdPeriph_Driver/inc
INCLUDE_PATH += $(BUILD_CORE_LIB_PATH)/platform/MCU/$(LEVEL2)/STM32_USB_Device_Driver/inc
INCLUDE_PATH += $(BUILD_CORE_LIB_PATH)/platform/NET/$(LEVEL3)/$(LEVEL3)_Host_Driver
INCLUDE_PATH += $(BUILD_CORE_LIB_PATH)/communication/lib/tropicssl/include/tropicssl
INCLUDE_PATH += $(BUILD_CORE_LIB_PATH)/bootloader/src/core-v2
INCLUDE_PATH += $(BUILD_CORE_LIB_PATH)/wiring/inc
INCLUDE_PATH += $(APPLICATION_PATH)/firmware/services/inc
INCLUDE_PATH += $(BUILD_CORE_LIB_PATH)/build/arm/startup
# Local libraries paths to be added in step2.mk


# Flags for gcc, g++ and linker
# ----------------------------------
#
# Common CPPFLAGS for gcc, g++, assembler and linker
#
#CPPFLAGS     = -MD -MP -MF  -Werror
CPPFLAGS     = -Wall -Wno-switch -fmessage-length=0 -fno-strict-aliasing
CPPFLAGS    += -g3 -gdwarf-2 -Os -$(MCU_FLAG_NAME)=$(MCU) -mthumb
CPPFLAGS    += $(OPTIMISATION) -ffunction-sections -fdata-sections -fno-builtin
CPPFLAGS    += $(FPU_OPTIONS)
CPPFLAGS    += $(addprefix -D, $(PLATFORM_TAG) $(BUILD_OPTIONS) $(MORE_OPTIONS) $(D_OPTIONS))
CPPFLAGS    += $(addprefix -I, $(INCLUDE_PATH))
# Local libraries paths to be added in step2.mk

# Specific CFLAGS for gcc only
# gcc uses CPPFLAGS and CFLAGS
#
CFLAGS       = -std=c99 -std=gnu99 -Wno-pointer-sign

# Specific CXXFLAGS for g++ only
# g++ uses CPPFLAGS and CXXFLAGS
#
CXXFLAGS      = -std=gnu++11 -fno-exceptions -fno-rtti

# Specific ASFLAGS for gcc assembler only
# gcc assembler uses CPPFLAGS and ASFLAGS
#
ASFLAGS      = -Wa,--defsym -Wa,SPARK_INIT_STARTUP=1 -x assembler-with-cpp

# Specific LDFLAGS for linker only
# linker uses CPPFLAGS and LDFLAGS
#
#LDFLAGS       = -Wa,--defsym -Wa,SPARK_INIT_STARTUP=1 -xassembler-with-cpp
LDFLAGS       = -T$(BUILD_CORE_LIB_PATH)/build/arm/linker/$(LDSCRIPT)
LDFLAGS      += --specs=nano.specs -lc -lnosys -u _printf_float
LDFLAGS      += -nostartfiles -Xlinker --gc-sections

# Specific OBJCOPYFLAGS for objcopy only
# objcopy uses OBJCOPYFLAGS only
#
OBJCOPYFLAGS  = -Obinary
TARGET_HEXBIN = $(TARGET_BIN)


