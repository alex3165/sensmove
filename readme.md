<h1 align="center">SENSMOVE</h1>
<br/>
<p align="center">
  <img title="sensmove" src='design/logo.png' width="180px"/>
</p>
<br/>
<p align="center">
  <a href='https://gitter.im/alex3165/sensmove'>
    <img src='https://badges.gitter.im/Join%20Chat.svg' alt='Gitter Chat' />
  </a>
</p>

## About

[![Join the chat at https://gitter.im/alex3165/sensmove](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/alex3165/sensmove?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)
Sensmove is the first open-sourced smart insole system that allows the user to visualize his foot forces simultaneously on his smartphone.
The smart insole get 7 force sensitive resistor sensors distributed along it. The data are sent through bluetooth to the mobile phone. Then, on the smartphone, when the user starts a new session, he can visualize his foot forces through circle charts. Once the session is finished, a summary is done and the user can grasp a session name, an activity and a description. The user can also see his profile and the history of the differents sessions he did.

<p align="center">
  <img title="sensmove" src='documentation/prototype_sensmove.jpg' width="500px"/>
</p>

## Possibles use cases
- Sport: Help sportsmen in their trainning by coaching them trough an application and check that the person performs his exercises correctly. Check your strides.
- Music: Use it as a loop pedal or metronome.
- Robotic: Stabilize balance of a robot.
- Medical: Help podologs to design better insole, help kinesitherapists to follow their patients during their consultation.


## Technologies
- Arduino environment for insole electronic system.
- IOS environment for mobile application development.


## Improvements
Lots of improvements should be done, it is a first draft of the possibilities of the insole, the final purpose is to provide as usable as possible smart insole system.
Main improvements should be done on the bluetooth transmission processing on ios. Another important work to do is to store force data on the smartphone with SQLite mobile database.

Improvements should be done on mobile application and on hardware, if you want to improve it, you can check github issues for details.


## Documentation
All the documentation can be found in documentation folder.
