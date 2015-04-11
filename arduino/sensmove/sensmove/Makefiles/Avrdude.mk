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
# Last update: Nov 12, 2014 release 233


# AVRdude
# ----------------------------------
#
# First /dev port
#
# Port is no longer managed here but AVRDUDE_PORT is required for serial console
#
AVRDUDE_PORT            = $(firstword $(wildcard $(BOARD_PORT)))

ifndef AVRDUDE_PATH
    AVRDUDE_PATH        = $(APPLICATION_PATH)/hardware/tools/avr
endif

ifndef AVRDUDE
    AVRDUDE_EXEC        = $(AVRDUDE_PATH)/bin/avrdude
endif

ifndef AVRDUDE_CONF
    AVRDUDE_CONF        = $(AVRDUDE_PATH)/etc/avrdude.conf
endif

ifndef AVRDUDE_COM_OPTS
    AVRDUDE_COM_OPTS    = -q -V -F -p$(MCU) -C$(AVRDUDE_CONF)
endif

# Normal programming info
#
ifeq ($(AVRDUDE_PROGRAMMER),)
    AVRDUDE_PROGRAMMER  = $(call PARSE_BOARD,$(BOARD_TAG),upload.protocol)
endif

ifeq ($(AVRDUDE_BAUDRATE),)
    AVRDUDE_BAUDRATE    = $(call PARSE_BOARD,$(BOARD_TAG),upload.speed)
endif

#ifndef AVRDUDE_OPTS
ifeq ($(AVRDUDE_OPTS),)
#    AVRDUDE_OPTS      = -c$(AVRDUDE_PROGRAMMER) -b$(AVRDUDE_BAUDRATE) -P$(AVRDUDE_PORT)
    ifeq ($(AVRDUDE_BAUDRATE),)
        AVRDUDE_OPTS    = -D -c$(AVRDUDE_PROGRAMMER)
    else
        AVRDUDE_OPTS    = -D -c$(AVRDUDE_PROGRAMMER) -b$(AVRDUDE_BAUDRATE)
    endif
endif

ifndef AVRDUDE_MCU
    AVRDUDE_MCU         = $(MCU)
endif

ifndef ISP_PROG
    ISP_PROG            = -c stk500v2
endif

ifneq ($(ISP_PORT),)
    AVRDUDE_ISP_OPTS    = -P $(ISP_PORT) $(ISP_PROG)
else
    AVRDUDE_ISP_OPTS    = $(ISP_PROG)
endif

# fuses if you're using e.g. ISP
#
ifndef ISP_LOCK_FUSE_PRE
    ISP_LOCK_FUSE_PRE   = $(call PARSE_BOARD,$(BOARD_TAG),bootloader.unlock_bits)
endif

ifndef ISP_LOCK_FUSE_POST
    ISP_LOCK_FUSE_POST  = $(call PARSE_BOARD,$(BOARD_TAG),bootloader.lock_bits)
endif

ifndef ISP_HIGH_FUSE
    ISP_HIGH_FUSE       = $(call PARSE_BOARD,$(BOARD_TAG),bootloader.high_fuses)
endif

ifndef ISP_LOW_FUSE
    ISP_LOW_FUSE        = $(call PARSE_BOARD,$(BOARD_TAG),bootloader.low_fuses)
endif

ifndef ISP_EXT_FUSE
    ISP_EXT_FUSE        = $(call PARSE_BOARD,$(BOARD_TAG),bootloader.extended_fuses)
endif

ifndef VARIANT
    VARIANT             = $(call PARSE_BOARD,$(BOARD_TAG),build.variant)
endif
