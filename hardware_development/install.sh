
##
##  Link arduino librairies from the project to the Arduino libraries folder
##  initializer.sh
##  SensMove
##
##  @author Jean-Sebastien Pélerin
##  @date 13/03/2015
##  @copyright (c) 2014 SensMove. All rights reserved.
##

#The script is meant to work on OSX

#Path of the libraries in the project (the script should be started in the root of the project)
PATHPROJECT="${PWD}/libraries/"
#Path of the libraries in Arduino
PATHARDUI="${HOME}/Documents/Arduino/Libraries/"

#Color added to the console messages
red='\033[0;31m'
blue='\033[0;34m'
cyan='\033[0;36m'
NC='\033[0m' # Uncolored

#Check wether the path specified exists
if ! [ -d "$PATHARDUI" ]; then

	echo -e "${red}Error, path $PATHARDUI does not exist${NC}"

elif ! [ -d "$PATHPROJECT" ]; then

	echo -e "${red}Error, path $PATHPROJECT does not exist${NC}"

else

	for file in $(ls -1 $PATHPROJECT); do 
	#For each folder in the library folder of the project


		if [ -L "$PATHARDUI${file%/}" ]; then 
		#Link exists in Arduino's Library
			
			echo -e "${cyan}Link ${file%/} exists ${NC}"

		elif [ -d "$PATHARDUI${file%/}" ]; then 
		#Foder exists in Arduino's Library

			echo -e "${red}Warning, folder ${file%/} already exists in Arduino's library, the folder will not be updated"

		else 
		#Link has to be created

			echo -e "${blue}Adding symbolic link ${file%/} ${NC}"
			ln -s $PATHPROJECT$file $PATHARDUI
		fi

	done
fi