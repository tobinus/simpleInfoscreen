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
 * Reply true if the client should refresh itself, false otherwise.
 * User: thorben
 * Date: 16.07.15
 * Time: 20:57
 */

namespace {
    require_once 'prepend.php';

    function noUpdateIfError($errno, $errfile) {
        if (in_array($errno, [
            E_NOTICE,
            E_USER_NOTICE,
            E_DEPRECATED,
            E_USER_DEPRECATED,
            E_STRICT
        ])) {
            return false;
        }

        die("false");
    }

    set_error_handler('noUpdateIfError');
}


namespace tobinus\SimpleInfoscreen
{
    require INCDIR . '/include.php';
    // Make sure we encounter the same errors as index.php
    // This way, the infoscreen won't constantly try (and fail) to update,
    // since our error handler says there's no update when an error occurs.
    global $SETTINGS;
    // Work around lazy loading
    $SETTINGS->title;
    // Try to load the relevant slide show
    $scheduler = new Scheduler($SETTINGS->enableScheduling);
    SlideShowFile::open(SLIDESHOW_FILE)->getSlideShow(
        $scheduler->getSlideShowToUse($SETTINGS)
    );

    // Validate data from client
    $datetime = $_GET['datetime'];

    if (!is_numeric($datetime)) {
        echo "malformed request. datetime expected";
        exit;
    } else {
        // Has anything been modified since the infoscreen was last updated?
        if (getLastModifiedTime($scheduler) > $datetime) {
            echo "true";
        } else {
            echo "false";
        }
    }
}