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
 * @file Accepts error details through POST, and logs them.
 * Used by JavaScript error handler, which sends AJAX requests to this page
 * when an error occurs.
 *
 * TODO: It is possible for anyone to make the error log as big as he wishes, by forging POST-requests with much data/high frequency. Denial of service attack?
 */
require_once 'prepend.php';
require INCDIR . '/include.php';

if (!$SETTINGS->enableJSErrorReporting) {
    header("HTTP/1.1 403 Forbidden");
    die('403 Forbidden');
}

function v($varName)
{
    return str_replace(
        "\0",
        "",
        htmlspecialchars(
            filter_input(
                INPUT_POST,
                $varName,
                FILTER_SANITIZE_STRING
            )
        )
    );
}

//

$message = v('message');
$url = v('url');
$line = v('line');
$column = v('column');

error_log(\tobinus\ErrorHandler\generateLogMessage(
    $message,
    $url,
    $line,
    $column
), 3, LOG_FILENAME);