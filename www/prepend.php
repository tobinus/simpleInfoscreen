<?php
/*
The MIT License (MIT)

Copyright (c) 2015 Thorben Werner Sjøstrøm Dahl

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/

/**
 * Defines constants that are used when accessing the different
 * directories. Change those if your directory structure differs from the default.
 *
 * Warning: Any errors here will not be reported, since this file is intended
 * to be executed before any error handlers are in place. Double-check to make
 * sure the syntax is right, the paths are correct etc before uploading changes.
 * Test the website afterwards.
 *
 * Note: __DIR__ is evaluated as the directory in which THIS file is located,
 * without the trailing slash. Use relative paths, e.g. __DIR__ . '/../config'
 * if the config directory is located in the parent directory. Remove '__DIR__ .'
 * if you want to use absolute paths.
 *
 * Similarly, OUTSIDE_WEBROOT is the path to the 'infoscreen_outside_web' directory,
 * without the trailing slash. Change local.php to change that path.
 *
 * You might want to read up on PHP syntax if you're not familiar:
 * http://php.net/manual/en/language.types.string.php
 * http://php.net/manual/en/language.operators.string.php
 * http://php.net/manual/en/function.define.php
 *
 * @package tobinus\SimpleInfoscreen
 * @author Thorben Werner Sjøstrøm Dahl <thorben@sjostrom.no>
 * @copyright 2015 Thorben Werner Sjøstrøm Dahl
 */
namespace tobinus\SimpleInfoscreen;
/**
 * Directory in which the application's main files resides (the place which
 * users visit on the webserver).@global
 */
define('MAINDIR',__DIR__);

/**
 * Load the definition of {@link OUTSIDE_WEBROOT}.
 */
require('local.php');
/**
 * The directory in which configuration files can be found.
 * MUST be outside of web root (that is, users MUST NOT be able to visit
 * this folder through the webserver).@global
 */
define('CONFIGDIR',OUTSIDE_WEBROOT . '/config');

/**
 * Directory for all classes that are part of \tobinus namespace, used when
 * autoloading classes. This directory MUST contain SimpleInfoscreen and more,
 * and MUST be outside the web root.@global
 */
define('CLASSDIR', OUTSIDE_WEBROOT . '/classes');

/**
 * Directory for miscellaneous files which PHP create, like error.log and cache
 * files. As such, the following requirements apply:
 * * The web server (and thus PHP) MUST have write access to this directory.
 * * The directory MUST be outside the web root.@global
 */
define('DATADIR', OUTSIDE_WEBROOT . '/data');

/**
 * Directory for html-files that are displayed on the infoscreen (using load.php).
 * In order to make it possible for PHP to automatically generate this content,
 * the following requirements apply:
 * * The web server (and thus PHP) MUST have write access to this directory.
 * * The directory MUST be outside the web root.@global
 */
define('LOCALDIR', MAINDIR . '/local');

/**
 * Directory for the Twig folder. Not used yet. MUST be outside the web root.@global
 */
define('TWIGDIR', OUTSIDE_WEBROOT . '/Twig');

/**
 * Directory for the include folder, in which you find common PHP code which is
 * not a part of a class (and therefore not found in the classes directory).
 * MUST be outside the web root.@global
 */
define('INCDIR', OUTSIDE_WEBROOT . '/include');

/**
 * Directory where templates are located. You know where NOT to put it ;)@global
 */
define('TEMPLATEDIR', OUTSIDE_WEBROOT . '/templates');

/**
 * True if this is a production environment, false if under
 * development/debugging @global
 * @name bool $PRODUCTION
 */
$PRODUCTION = boolval(parse_ini_file(CONFIGDIR . '/production.ini')['production']);
