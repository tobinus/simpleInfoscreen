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
 * Will load local files with correct headers attached.
 * @package tobinus\SimpleInfoscreen
 */

namespace {
    require_once('prepend.php');

    /**
     * Returns the absolute path to the file which should be outputted, or false
     * if it cannot be outputted.
     * @return bool|string
     */
    function getFileToRead()
    {
        $fileToRead = realpath(getRawFileToRead());
        if ($fileToRead && strpos($fileToRead, realpath('local')) === 0 && !is_dir($fileToRead) && is_file($fileToRead) && is_readable($fileToRead)) {
            return $fileToRead;
        } else {
            return false;
        }
    }

    /**
     * Returns the potential absolute path to the file which should be outputted.
     * @return bool|string The absolute path to the file which is requested, or
     * false if the URL is malformed or doesn't have an extension.
     */
    function getRawFileToRead()
    {
        $prefix = '.*\/load\.php\/';
        $optionalFolders = '((?:[\w\d]+\/)*)';
        $filename = '([\w\d\.]+\.[\w]+)';
        $requestUri = preg_filter("/^${prefix}${optionalFolders}${filename}\$/", '/$1$2', $_SERVER['REQUEST_URI'], 1);
        if ($requestUri === null) {
            return false;
        }
        return LOCALDIR . $requestUri;
    }

    /**
     * Error handler which just prints out the requested file.
     * @param int $errno Severity.
     * @return bool
     */
    function keepItSimpleStupid($errno)
    {
        global $PRODUCTION;
        // Is this for us?
        if (in_array($errno, [
            E_USER_DEPRECATED,
            E_DEPRECATED,
            E_NOTICE,
            E_USER_NOTICE,
            E_STRICT
        ]) || !$PRODUCTION) {
            // Nope, let someone else do it
            return false;
        }

        global $_GET;

        // WE ARE DESPERATE, ignore 403 not modified stuff

        // Read the file!
        $fileToRead = getFileToRead();
        if ($fileToRead) {
            // Note: templates will be outputted as-is (not executed)!
            readfile($fileToRead);
            return true;
        } else {
            return false;
        }
    }

    set_error_handler('keepItSimpleStupid');
}

namespace tobinus\SimpleInfoscreen
{

    /**
     * Find the mime type to be used, based on the given file extension.
     * @param string $fileExtension File extension which determines the mime type.
     * Do not include the dot.
     * @return string Mime type, which can be used in a Content-Type header.
     */
    function getMimeType($fileExtension)
    {
        $fileExtension = mb_strtolower($fileExtension);
        switch ($fileExtension) {
            case 'html':
            case 'htm':
            case 'twig':
                return 'text/html';
            case 'jpg':
            case 'jpeg':
                return 'image/jpeg';
            case 'png':
                return 'image/png';
            case 'css':
                return 'text/css';
            case 'js':
                return 'text/javascript';
            case 'pdf':
                return 'application/pdf';
            case 'vob':
                return 'video/dvd';
            case 'mp4':
                return 'video/mp4';
            case 'gif':
                return 'image/gif';
            case 'wmv':
                return 'video/x-ms-wmv';
            default:
                trigger_error("Unknown file extension $fileExtension");
                return 'text/plain';
        }
    }

    /**
     * Send correct Content-Type headers, based on the given file extension.
     * @param string $fileExtension File extension to use when determining the
     * mime type. Do not include the first dot.
     */
    function sendMimeType($fileExtension)
    {
        $mimeType = getMimeType($fileExtension);
        header("Content-Type: $mimeType");
    }


    require INCDIR . '/include.php';

    global $SETTINGS;
    global $PRODUCTION;
// Are the parameters valid?

    $fileToRead = getFileToRead();

    if (!$fileToRead) {
        header("Cache-Control: private,max-age=0,no-cache");
        $referer = filter_input(INPUT_SERVER, 'HTTP_REFERER', FILTER_SANITIZE_URL);
        if (in_array($referer, [
            $SETTINGS->rootUrl . '/',
            $SETTINGS->rootUrl . '/index.php',
        ]) || !$PRODUCTION) {
            trigger_error('A slideshow tried to load the uri ' . filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL) .
                ', which was evaluated to '.getRawFileToRead().' and '.
                'subsequently not found', E_USER_ERROR);
        } else {
            \tobinus\ErrorHandler\displayErrorPage();
            die();
        }
    }


    $fileExtension = mb_substr($fileToRead, mb_strrpos($fileToRead, '.')+1);
    $isTemplate = (strtolower($fileExtension) == 'twig');
//get the last-modified-date of the requested file
    $lastModified = filemtime($fileToRead);

//get a unique hash of this file (etag)
    $etagFile = md5_file($fileToRead);
//get the HTTP_IF_MODIFIED_SINCE header if set
    $ifModifiedSince = (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : false);
//get the HTTP_IF_NONE_MATCH header if set (etag: unique file hash)
    $etagHeader = (isset($_SERVER['HTTP_IF_NONE_MATCH']) ? trim($_SERVER['HTTP_IF_NONE_MATCH']) : false);

    sendMimeType($fileExtension);
//set last-modified header
    header("Last-Modified: " . gmdate("D, d M Y H:i:s", $lastModified) . " GMT");
//set etag-header
    header("Etag: $etagFile");
//make sure caching is turned on
    header('Cache-Control: public,max-age=0,no-cache');

//check if page has changed. If not, send 304 and exit
    if (!$isTemplate && (@strtotime($ifModifiedSince) == $lastModified || $etagHeader == $etagFile)) {
        header("HTTP/1.1 304 Not Modified");
        die();
    }

// output the file
    if ($isTemplate) {
        $twig = Template::init();
        $twig->setLoader(new \Twig_Loader_Filesystem([LOCALDIR, TEMPLATEDIR]));
        $twig->display(basename($fileToRead));
    } else {
        readfile($fileToRead);
    }
}
