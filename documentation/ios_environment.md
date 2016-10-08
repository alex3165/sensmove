# SensMove iOS App

For a better asynchronous handler, when receiving data from bluetooth or when processing user service data we are using reactive cocoa library.

To manage Objective-C packages we are using cocoapod, for swift packages we simply drag & drop the library into the project library folder.

Currently, user informations are stored in a local json file "SMData.json" but on a long term vision, the plan is to create a REST Api .

## User interface

The UI uses Autolayout to positionate the elements.

## Notes

Left insole data and accelerometer data are not processed. Have a look at the graphic mockup for a better understanding of the [final design](../design/app_exports).
