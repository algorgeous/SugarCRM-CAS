SugarCRM CAS Authentication
===========================

Original credit goes to David Burke
(http://davidmburke.com/2011/01/27/cas-in-sugarcrm/) who provided the original
CASAuthenticate class files.

Forked from Alfred Bacon's repo (https://github.com/algorgeous/SugarCRM-CAS).

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

    git clone git://github.com/csegarra/SugarCRM-CAS.git modules/Users/authentication/CASAuthenticate

2. Edit the config.php file

   Add a key for authenticationClass that is set to CASAuthenticate.

<pre>
  'authenticationClass' => 'CASAuthenticate',
</pre>

Add a 'cas' key to the $sugar_config array that is an array holding the values for
your CAS server.

<pre>
  'cas' => array(
    'library' => '/path/to/CAS/CAS.php',
    'hostname' => 'cas.example.com',
    'port' => 443,
    'uri' => 'cas',
    'changeSessionID' => FALSE,
    'proxies' => array(
      'https://proxy-cas.example.com',
    ),
    'logout_return_url' => 'http://sugar.url/',
  ),
</pre>

License
-------

Copyright 2015 gcoop - Cooperativa de Software Libre

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
