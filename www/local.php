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
 * File which defines variables that are special to this server/installation.
 * @package tobinus\SimpleInfoscreen
 * @author Thorben Werner Sjøstrøm Dahl <thorben@sjostrom.no>
 * @copyright 2015 Thorben Werner Sjøstrøm Dahl
 * @license http://opensource.org/licenses/MIT The MIT License */

if (basename($_SERVER['SCRIPT_FILENAME']) == basename(__FILE__)) {
    die();
}

/**
 * Pathname to the `private`-folder, which should be placed
 * outside of the web root. @global
 */
define('OUTSIDE_WEBROOT', realpath(__DIR__ . '/' .
    /* Please write the path to your 'private' folder, relative to
     * this file. Don't include the trailing slash.
     * Example:
    'relative/path/to/private'
     */
    '../private'
));

/**
 * Timezone to be used. See the available timezones at <http://php.net/manual/en/timezones.php>
 */
date_default_timezone_set("Europe/Oslo");
