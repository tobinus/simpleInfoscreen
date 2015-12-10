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
 * This document loads the slideshow in an iframe. That way, if the page fails
 * to load, we can reload it again. Thus, there is no need to manually refresh
 * the browser on error.
 * @package tobinus\SimpleInfoscreen
 * TODO: Make it possible for this page to be reloaded when update is available
 */
namespace tobinus\SimpleInfoscreen;

require('prepend.php');
require(INCDIR . '/include.php');
global $SETTINGS;

$lastUpdate = max(filemtime(__FILE__), filemtime(TEMPLATEDIR . '/index.twig'));

if (!empty($_GET['checkUpdate']) && is_numeric($_GET['checkUpdate'])) {
    if ($lastUpdate > intval($_GET['checkUpdate'])) {
        die('true');
    } else {
        die('false');
    }
}

$twig = Template::init();
$twig->display('index.twig', [
    'title' => $SETTINGS->title,
    'query' => parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY),
    'lastUpdate' => $lastUpdate,
    // Check for updates every 5th time displaySlideShow does
    'updateCheckInterval' => ($SETTINGS->secondsBetweenUpdateChecks * 5) - 1,
]);