CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Features
 * Maintainers


INTRODUCTION
------------

The User Token Login module allows to generate a random token for new and
existing users and login a user via token.


REQUIREMENTS
------------

No other module required.


INSTALLATION
------------

 * Install as you would normally install a custom/contributed Drupal module. Visit:
   https://www.drupal.org/node/1897420 for further information.


CONFIGURATION
-------------

1. Navigate to Administration > Configuration > Account settings > Manage fields tab.
2. Auth Token field should be created.
3. Navigate to Administration > Configuration > Token generate settings.
4. Update token length configuration, the default token length is 32.
5. Update and save the configuration.


FEATURES
--------

On module installation, a token will be generated for all the existing users.
It shall also generate a token for every new user created.
A user shall be able to login provided the token stands valid and it does exist in the system.
www.example.com?authtoken=[TOKEN VALUE]

MAINTAINERS
-----------

..........
