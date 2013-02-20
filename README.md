Mitsuba
=======

Project Mitsuba is an opensource (GPLv3 licensed) image board software written in PHP and using MySQLi.

Installation
============

To install Mitsuba you'll have to:
* Import database.sql into your MySQLi database
* Edit config.php
* chmod 777 ./ (only on UNIX-like filesystems)
* Go to ./mod.php and login as root:root
* Configure boards in Manage boards
* Change password

Planned features
================

* logging actions
* wordfilter
* post edition
* alt. stylesheets
* better design
* bbcode/spoilers
* installer
* autosage/bumplimit
* captcha support
* automatic post deletion
* post delete by ip
* file hash match
* plugin support
* catalog feature (like 4chan)
* floodlimits/spam detector