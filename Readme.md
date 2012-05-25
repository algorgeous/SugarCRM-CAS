SugarCRM CAS Authentication
===========================

Original credit goes to David Burke
(http://davidmburke.com/2011/01/27/cas-in-sugarcrm/) who provided the original
CASAuthenticate class files.

This is a class for SugarCRM (http://sugarcrm.com) that allows you to
authenticate with a CAS server.

phpCAS is an authentication library that allows PHP applications to easily
authenticate users via a Central Authentication Service (CAS) server.

Please see the phpCAS website for more information:

https://wiki.jasig.org/display/CASC/phpCAS

You will need to download the phpCAS libraries to your web server.

Installation
============

1. Place the files into modules/Users/authentication/CASAuthenticate directory.

git clone git://github.com/algorgeous/SugarCRM-CAS.git modules/Users/authentication/CASAuthenticate

2. Edit the config.php file
Add a key for authenticationClass that is set to CASAuthenticate.

  'authenticationClass' => 'CASAuthenticate',

Add a 'cas' key to the $sugar_config array that is an array holding the values for
your CAS server.

  'cas' => array(
    'library' => '/path/to/CAS/CAS.php',
    'hostname' => 'cas.example.com',
    'port' => 443,
    'uri' => 'cas',
    'changeSessionID' => FALSE,
  ),
