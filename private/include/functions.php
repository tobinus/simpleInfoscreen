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
 * File with common functions that it wouldn't make sense to put in a class.
 * @author Thorben Werner Sjøstrøm Dahl <thorben@sjostrom.no>
 * @copyright Thorben Werner Sjøstrøm Dahl 2015
 * @license http://opensource.org/licenses/MIT The MIT License
 * @package tobinus\SimpleInfoscreen
 */

namespace tobinus\SimpleInfoscreen;

require_once( INCDIR . '/include.php');

/**
 * Get the last modification Unix time.
 * Use to compare whether a given infoscreen has gone stale.
 * @return int Unix time of the most recent modification.
 * TODO: Make the update check more intelligent, by comparing settings, slides etc (changes in config file might not affect infoscreens)
 */
function getLastModifiedTime(Scheduler $scheduler)
{
    global $SETTINGS;
    // TODO: Decrease the list of files to check when in production
    $filesToCheck = [
        MAINDIR . '/displaySlideShow.php',
        CONFIGDIR . '/settings.ini',
        CONFIGDIR . '/infoscreens.ini',
        CONFIGDIR . '/slideshows.ini',
        INCDIR . '/lastModification',
        MAINDIR . '/js/slideshow.js',
        MAINDIR . '/js/' . $SETTINGS->transition . '.transitions.SlideShow.js',
    ];

    return max(array_merge(
        array_map('filemtime', $filesToCheck),
        [$scheduler->getLastChange()]));
}

/**
 * If the given variable isn't already set, it will be set to the given default.
 * See {@link http://stackoverflow.com/a/5979751}.
 * @param $var mixed Variable to assign to, if not set
 * @param $default_var mixed Default value to use if $var is not set
 * @return bool true if $var is changed, false otherwise
 */
function set_unless_defined(&$var, $default_var){
    if (! isset($var)){
        $var = $default_var;
        return true;
    } else {
        return false;
    }
}

/**
 * Same as {@link set_unless_defined()}, except $default_var is assigned to $var
 * if and only if $var is empty (so $var might be overridden when set).
 * @param $var
 * @param $default_var
 * @return bool
 */
function set_if_empty(&$var, $default_var)
{
    if (empty($var)) {
        $var = $default_var;
        return true;
    } else {
        return false;
    }
}

/**
 * Return the given value, unless it is not set, in which the default is used.
 * $currentValue is passed by reference, but it is only tested, not modified.
 * @param mixed $currentValue The variable to test and possibly use.
 * @param mixed $defaultVar The default to use if $currentValue is not set.
 * @param bool|false $testEmpty true: test whether $currentValue is empty.
 * false and default: test whether $currentValue is set.
 * @return mixed $currentValue if it is set (or not empty), $defaultValue if not.
 */
function applyDefault(&$currentValue, $defaultVar, $testEmpty = false)
{
    if ((!$testEmpty && !isset($currentValue)) || ($testEmpty && empty($currentValue))) {
        return $defaultVar;
    } else {
        return $currentValue;
    }
}