# How to craft the insole

Here is a list of some required equipments to craft the insole :

- Soldering iron
- Tin
- Breadboard
- Son drivers
- Potentiometer
- Multimeter

## Needed Components

For a single insole design you will need these components :

- 1 Standard insole (feel free to share your own design)
- 1 [sewing thread driver](http://www.adafruit.com/product/641)
- 1 [Arduino micro](http://www.adafruit.com/products/1315)
- 1 [Bluefruit LE](http://www.adafruit.com/products/1697)
- 1 [Battery lithium Ion](http://www.adafruit.com/products/258)
- 1 [Battery backpack Add-On](http://www.adafruit.com/products/2124)
- 7 [Force-Sensitive Resistor](http://www.adafruit.com/products/166)
- 1 [Resistor kit](https://www.sparkfun.com/products/10969)

Note that sensors are quickly satured, we identified better sensors from [Tekscan](https://www.tekscan.com/products-solutions/force-sensors/ess301) but we haven't tested it yet (every insight on these sensors are welcome).


## Design phase

> We advise you to use Fritzing software for the electronic design schema. [Fritzing](http://fritzing.org/home/)

You can follow the [design schema we created](source/electronic_design.fzz).

When crafting the insole, I would advice to do the following steps: Sew 2 conductives lignes on the insole with sewing thread driver, one line for the +5v and another for the mass and connect the sensors accordingly using the design schema.
