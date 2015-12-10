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
 * This is the one file other files should include (other than prepend.php).
 *
 * It:
 * * sets up the error handler,
 * * the autoloader,
 * * loads common functions,
 * * Set ups $SETTINGS and SLIDESHOW_FILE.
 * @author Thorben Werner Sjøstrøm Dahl <thorben@sjostrom.no>
 * @copyright Thorben Werner Sjøstrøm Dahl 2015
 * @license http://opensource.org/licenses/MIT The MIT License
 * @package tobinus\SimpleInfoscreen
 */

namespace
{
    ini_set("display_errors", "on");
    error_reporting(E_ALL);
    require CLASSDIR . '/ErrorHandler/ErrorHandler.php';

    register_shutdown_function( 'tobinus\ErrorHandler\check_for_fatal' );
    $previousErrorHandler = set_error_handler( 'tobinus\ErrorHandler\log_error' );
    set_exception_handler( 'tobinus\ErrorHandler\log_exception' );

    ini_set( "display_errors", "off" );
    error_reporting( E_ALL );


    // Load the autoloader!
    require INCDIR . '/Psr4AutoloaderClass.php';

    $loader = new tobinus\SimpleInfoscreen\Psr4AutoloaderClass();
    $loader->register();
    $loader->addNamespace('tobinus', CLASSDIR);
}
namespace tobinus\SimpleInfoscreen
{
    // Load common functions
    require INCDIR . '/functions.php';

    // Load settings
    /**
     * Application settings read from infoscreen.ini
     * @global Settings $SETTINGS
     * @name $SETTINGS
     */
    $SETTINGS = new Settings(true);

    /**
     * Path of slideshow configuration file.
     */
    define('SLIDESHOW_FILE', CONFIGDIR . '/slideshows.ini');

}