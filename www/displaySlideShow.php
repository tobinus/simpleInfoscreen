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
 * Send infoscreen to the client.
 * This script will generate all the HTML necessary to run the infoscreen, and
 * is called whenever a client wants to update or is started. A cache is kept,
 * mostly so that the infoscreens won't go down in case of an error.
 * @author Thorben Dahl <thorben@sjostrom.no>
 * @copyright Thorben Dahl 2015
 * @license The MIT License (MIT)
 * @package tobinus\SimpleInfoscreen
 */
namespace {
    require_once('prepend.php');
define('INDEX_CACHE_FILE', DATADIR . '/index.php.cache');
    /**
     * @param $errno
     * @param $errstr
     * @return bool
     */
function readFromCacheIfError($errno, $errstr)
{
    global $PRODUCTION;
    // Do we need to do anything?
    if (in_array($errno, [
        E_USER_DEPRECATED,
        E_DEPRECATED,
        E_NOTICE,
        E_USER_NOTICE,
        E_STRICT
    ])) {
        // Nope, let someone else do it
        return false;
    }
    // Can we read from a cache-file?
    if ($PRODUCTION && file_exists(INDEX_CACHE_FILE) && is_readable(INDEX_CACHE_FILE)) {
        // Discard any buffered output
        ob_end_clean();
        // Output it!
        readfile(INDEX_CACHE_FILE);
        // Halt execution
        die();
    } elseif ($PRODUCTION && function_exists('\tobinus\ErrorHandler\displayErrorPage')) {
        ob_end_clean();
        \tobinus\ErrorHandler\displayErrorPage();
        die();
    } elseif ($PRODUCTION && file_exists($errorFile = CLASSDIR . '/ErrorHandler/errorPage.php') && is_readable($errorFile)) {
        // Discard any buffered output
        ob_end_clean();
        // No cache, but we can output the error page ^_^
        global $SETTINGS;
        $url = isset($SETTINGS) ? $SETTINGS->rootUrl : basename(__DIR__);
        include($errorFile);
        die();
    } else {
        return false;
    }
}
set_error_handler('readFromCacheIfError');
}

namespace tobinus\SimpleInfoscreen
{

    require_once INCDIR . '/include.php';
    global $SETTINGS;

    // Should we use a specific infoscreen?
    $infoscreenToUse = 'default';
    if (!empty($_GET['i'])) {
        // Yes, validate
        $infoscreenToUse = mb_eregi_replace('[^\w\d]+', '', $_GET['i']);

        try {
            $SETTINGS->loadInfoscreen($infoscreenToUse);
        } catch (\RuntimeException $e) {
            // Not present in file, silently ignore
            $infoscreenToUse = 'default';
        }
    }

    $scheduler = new Scheduler($SETTINGS->enableScheduling);
    $lastModified = max(getLastModifiedTime($scheduler), strtotime('-1 hour'));
    $cacheFile = new Cache("displaySlideShow_$infoscreenToUse.html", $lastModified);
    try {
        $cacheFile->output();
    } catch (\RuntimeException $e) {
        $twig = Template::init();
        $slideShow = SlideShowFile::open(SLIDESHOW_FILE)->getSlideShow(
            $scheduler->getSlideShowToUse($SETTINGS)
        );
        $context = [
            'title' => $SETTINGS->title,
            'slideShowJs' => $slideShow->generateJavaScript(), // TODO: Leave this stuff to the template, send slideshow in
            'slideLoadTimeout' => $SETTINGS->slideLoadTimeout,
            'slideShowSize' => $slideShow->size(),
            'secondsBetweenUpdateChecks' => $SETTINGS->secondsBetweenUpdateChecks,
            'transition' => $SETTINGS->transition,
            'lastModified' => $lastModified,
        ];
        if ($context['slideShowSize'] == 1) {
            $context['showSlideNumber'] = false;
            $context['showSlideNumberJs'] = 'false';
            $context['useProgressBar'] = false;
            $context['useProgressBarJs'] = 'false';
        } else {
            $context['showSlideNumber'] = $SETTINGS->showSlideNumber;
            $context['showSlideNumberJs'] = $context['showSlideNumber'] ? 'true' : 'false';
            $context['useProgressBar'] = $SETTINGS->useProgressBar;
            $context['useProgressBarJs'] = $context['useProgressBar'] ? 'true' : 'false';
        }

        $output = $twig->render(
            'displaySlideShow.twig',
            $context
        );
        $cacheFile->write($output);
        $cacheFile->enableBrowserCaching();
        echo $output;
    }
}