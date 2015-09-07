# How to craft the insole

Before everythings, you should have some basics electronics components, some are not needed but highly recomended :

- Soldering iron
- Tin
- Breadboard
- Son drivers
- Potentiometer
- Multimeter


## Needed Components

For a single insole design you will need these components :

- 1 Classic insole (feel free to share your own design)
- 1 [sewing thread driver](http://www.adafruit.com/product/641)
- 1 [Arduino micro](http://www.adafruit.com/products/1315)
- 1 [Bluefruit LE](http://www.adafruit.com/products/1697)
- 1 [Battery lithium Ion](http://www.adafruit.com/products/258)
- 1 [Battery backpack Add-On](http://www.adafruit.com/products/2124)
- 7 [Force-Sensitive Resistor](http://www.adafruit.com/products/166)
- 1 [Resistor kit](https://www.sparkfun.com/products/10969)

Note that sensors are satured quickly, we identified better sensors on [Tekscan](https://www.tekscan.com/products-solutions/force-sensors/ess301) but haven't tested yet (every insight on this sensors is welcome).


## Design phase

You should follow the design scheme file "electronic_design.fzz" in source folder to connect components on the breadboard.

For the insole part, my advice is: Sew 2 conductives lignes on the insole with sewing thread driver, one line for the +5v and another for the mass. Connect the sensors by following the design scheme.