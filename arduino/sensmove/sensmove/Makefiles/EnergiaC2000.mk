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



# Energia LaunchPad C2000 specifics
# ----------------------------------
#
APPLICATION_PATH := $(ENERGIA_PATH)
ENERGIA_RELEASE  := $(shell tail -c2 $(APPLICATION_PATH)/lib/version.txt)
ARDUINO_RELEASE  := $(shell head -c4 $(APPLICATION_PATH)/lib/version.txt | tail -c3)

ifeq ($(shell if [[ '$(ENERGIA_RELEASE)' -ge '14' ]] ; then echo 1 ; else echo 0 ; fi ),0)
    $(error Energia release 14 required.)
endif

PLATFORM         := Energia
BUILD_CORE       := c2000
PLATFORM_TAG      = ENERGIA=$(ENERGIA_RELEASE) ARDUINO=$(ARDUINO_RELEASE) EMBEDXCODE=$(RELEASE_NOW) $(filter __%__ ,$(GCC_PREPROCESSOR_DEFINITIONS))


UPLOADER        = serial_loader2000
UPLOADER_PATH   = $(APPLICATION_PATH)/hardware/c2000/serial_loader2000/macos
UPLOADER_EXEC   = $(UPLOADER_PATH)/serial_loader2000
UPLOADER_OPTS   = -k $(APPLICATION_PATH)/hardware/c2000/F28027_flash_kernel/Debug/flash_kernel.txt -b 38400
UPLOADER_RESET  =
RESET_MESSAGE   = 1


APP_TOOLS_PATH   := $(APPLICATION_PATH)/hardware/tools/c2000/bin
CORE_LIB_PATH    := $(APPLICATION_PATH)/hardware/c2000/cores/c2000
APP_LIB_PATH     := $(APPLICATION_PATH)/hardware/c2000/libraries
BOARDS_TXT       := $(APPLICATION_PATH)/hardware/c2000/boards.txt

BUILD_CORE_LIB_PATH  = $(APPLICATION_PATH)/hardware/c2000/cores/c2000

#BUILD_CORE_LIBS_LIST    = $(subst .h,,$(subst $(BUILD_CORE_LIB_PATH)/,,$(wildcard $(BUILD_CORE_LIB_PATH)/*.h))) # */

c101                    = $(BUILD_CORE_LIB_PATH)/f2802x_common/source
c101                   += $(BUILD_CORE_LIB_PATH)/f2802x_headers/source

c102                    = $(foreach dir,$(c101),$(wildcard $(dir)/*.c)) # */
BUILD_CORE_C_SRCS       = $(filter-out %/$(EXCLUDE_LIST),$(c102))
c103                    = $(foreach dir,$(c101),$(wildcard $(dir)/*.cpp)) # */
BUILD_CORE_CPP_SRCS     = $(filter-out %/$(EXCLUDE_LIST),$(c103))
c104                    = $(foreach dir,$(c101),$(wildcard $(dir)/*.S)) # */
c105                    = $(filter-out %/$(EXCLUDE_LIST),$(c104))
BUILD_CORE_AS_SRCS      = $(sort $(c105))

BUILD_CORE_OBJ_FILES    = $(BUILD_CORE_AS_SRCS:.S=.S.o) $(BUILD_CORE_C_SRCS:.c=.c.o) $(BUILD_CORE_CPP_SRCS:.cpp=.cpp.o) 
BUILD_CORE_OBJS         = $(patsubst $(BUILD_CORE_LIB_PATH)/%,$(OBJDIR)/%,$(BUILD_CORE_OBJ_FILES))

FIRST_O_IN_A            = $(filter %/F2802x_asmfuncs.S.o,$(BUILD_CORE_OBJS))


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
CC      = $(APP_TOOLS_PATH)/cl2000
CXX     = $(APP_TOOLS_PATH)/cl2000
AR      = $(APP_TOOLS_PATH)/ar2000
#OBJDUMP = $(APP_TOOLS_PATH)/arm-none-eabi-objdump
OBJCOPY = $(APP_TOOLS_PATH)/hex2000
#SIZE    = $(APP_TOOLS_PATH)/arm-none-eabi-size
NM      = $(APP_TOOLS_PATH)/nm2000

BOARD    = $(call PARSE_BOARD,$(BOARD_TAG),board)
#LDSCRIPT = $(call PARSE_BOARD,$(BOARD_TAG),ldscript)
VARIANT  = $(call PARSE_BOARD,$(BOARD_TAG),build.variant)
VARIANT_PATH = $(APPLICATION_PATH)/hardware/c2000/variants/$(VARIANT)

LDSCRIPT = $(call PARSE_BOARD,$(BOARD_TAG),build.rts)
# Run-Time Support Library


OPTIMISATION     = -o3
OUT_PREPOSITION  = --output_file=


INCLUDE_PATH    := $(CORE_LIB_PATH) $(VARIANT_PATH)
INCLUDE_PATH    += $(APPLICATION_PATH)/hardware/tools/c2000
INCLUDE_PATH    += $(APPLICATION_PATH)/hardware/tools/c2000/include
INCLUDE_PATH    += $(BUILD_CORE_LIB_PATH)/f2802x_common/include
INCLUDE_PATH    += $(BUILD_CORE_LIB_PATH)/f2802x_headers/include
INCLUDE_PATH    += $(APPLICATION_PATH)/hardware/c2000/lib


# Flags for gcc, g++ and linker
# ----------------------------------
#
# Common CPPFLAGS for gcc, g++, assembler and linker
#
CPPFLAGS     = -v28 -ml -mt -g
CPPFLAGS    += $(addprefix --define=,F_CPU=$(F_CPU) $(MCU))
CPPFLAGS    += --gcc --diag_warning=225 --gen_func_subsections=on
CPPFLAGS    += --display_error_number --diag_wrap=off --preproc_with_compile
# --preproc_dependency

# Specific CFLAGS for gcc only
# gcc uses CPPFLAGS and CFLAGS
#
CFLAGS       = $(addprefix --define=, $(PLATFORM_TAG))
CFLAGS      += $(addprefix --include_path=,$(INCLUDE_PATH))

# Specific CXXFLAGS for g++ only
# g++ uses CPPFLAGS and CXXFLAGS
#
CXXFLAGS     = $(addprefix --define=, $(PLATFORM_TAG))
CXXFLAGS    += $(addprefix --include_path=,$(INCLUDE_PATH))

# Specific ASFLAGS for gcc assembler only
# gcc assembler uses CPPFLAGS and ASFLAGS
#
ASFLAGS      = --asm_extension=S
ASFLAGS     += $(addprefix --include_path=,$(INCLUDE_PATH))

# Specific LDFLAGS for linker only
# linker uses CPPFLAGS and LDFLAGS
#
LDFLAGS      = $(addprefix --define=, $(PLATFORM_TAG))
LDFLAGS     += -z --stack_size=0x300 --warn_sections
LDFLAGS     += --map_file=Builds/embeddedcomputing.map
LDFLAGS     += $(addprefix -i,$(INCLUDE_PATH))
LDFLAGS     += --reread_libs --display_error_number --diag_wrap=off --entry_point=code_start --rom_model


COMMAND_FILES = $(CORE_LIB_PATH)/f2802x_common/cmd/F28027.cmd $(CORE_LIB_PATH)/f2802x_headers/cmd/F2802x_Headers_nonBIOS.cmd


# Specific OBJCOPYFLAGS for objcopy only
# objcopy uses OBJCOPYFLAGS only
#
OBJCOPYFLAGS  = #
TARGET_HEXBIN = $(TARGET_TXT)
