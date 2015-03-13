#!/bin/bash
## Link Arduino librairires
##
## Made by Jean-Sebastien Pélerin
##
## Nobody is permitted to copy and distribute verbatim or modified
## copies of this license document, and changing it is allowed as long
## as the name is changed.
## DO NOT DO WHAT THE HELL YOU WANT TO PUBLIC LICENSE
## TERMS AND CONDITIONS FOR COPYING, DISTRIBUTION AND MODIFICATION
##
## 0. You just WILL NOT DO WHAT THE HELL YOU WANT TO.


PATHPROJECT="${PWD}/Arduino/libraries/*"
PATHARDUI="${HOME}/Documents/Arduino/libraries/"

##echo $PATHPROJECT
if ln -s $PATHPROJECT $PATHARDUI; then

echo "Lien vers librairie effectué"

else

echo "Erreur de chemin"

fi
