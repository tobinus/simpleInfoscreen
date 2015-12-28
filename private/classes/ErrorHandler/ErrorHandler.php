<?php
/**
 * Created by PhpStorm.
 * User: thorben
 * Date: 21.07.15
 * Time: 00:13
 */

namespace tobinus\ErrorHandler;

define('LOG_FILENAME', DATADIR . '/error.log');

/**
 * Flag indicating whether an error has occurred yet.
 * @global bool $ERROR_HAS_OCCURRED
 */
$ERROR_HAS_OCCURRED = false;

 /**
 * Error handler, passes flow over the exception logger with new ErrorException.
 */
function log_error( $num, $str, $file, $line, $context = null )
{
    log_exception( new \ErrorException( $str, 0, $num, $file, $line ) );
}

/**
 * @param $severity
 * @return string
 */
function getReadableSeverity( $severity)
{
    switch ($severity)
    {
        case E_NOTICE:
        case E_USER_NOTICE:
            return 'Notice';
        case E_STRICT:
            return 'Strict';
        case E_RECOVERABLE_ERROR:
            return 'Recoverable error';
        case E_DEPRECATED:
        case E_USER_DEPRECATED:
            return 'Deprecated';
        case E_WARNING:
        case E_USER_WARNING:
            return 'Warning';
        case E_CORE_WARNING:
            return 'Core Warning';
        case E_COMPILE_WARNING:
            return 'Compile Warning';
        case E_PARSE:
            return 'Parse Error';
        case E_ERROR:
        case E_USER_ERROR:
            return 'Fatal Error';
        case E_CORE_ERROR:
            return 'Core Error';
        case E_COMPILE_ERROR:
            return 'Compile Error';
        default:
            return 'Unknown Error';
    }
}

/**
 * Uncaught exception handler.
 */
function log_exception( \Exception $e )
{
    global $PRODUCTION, $previousErrorHandler, $ERROR_HAS_OCCURRED;

    $ERROR_HAS_OCCURRED = true;

    if ($e instanceof \ErrorException) {
        // Use severity
        $exceptionType = getReadableSeverity($e->getSeverity());
    } else {
        $exceptionType = get_class($e);
    }

    $headersSent = headers_sent();


    $logMessage = generateLogMessage(
        "$exceptionType: {$e->getMessage()}",
        $e->getFile(),
        $e->getLine()
    );
    error_log(filter_var($logMessage, FILTER_SANITIZE_STRING), 3, LOG_FILENAME);

    $isFatal = (!($e instanceof \ErrorException) || isFatal($e->getSeverity()));

    // Use the previous error handler, if it exists.
    // Continue if that error handler returns false
    if (is_callable($previousErrorHandler)) {
        if (call_user_func(
            $previousErrorHandler,
            ($e instanceof \ErrorException) ? $e->getSeverity() : E_ERROR,
            $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        )) {
            return true;
        }
    }

    if (!$headersSent && $isFatal) {
        $resetScript = '<script>setTimeout(function () {location.reload(true);}, 1*60*1000);</script>';
    } else {
        $resetScript = '';
    }
    if ( $PRODUCTION == false )
    {
        if (array_key_exists('printTrace', $_GET)) {
            $trace = htmlspecialchars(str_replace("    ", "  ", print_r(debug_backtrace(), true)), ENT_SUBSTITUTE);
            $trace = <<<END
<h4>Debug backtrace</h4>
</div>\n
<div style="width: 98vw; height: 60vh; overflow: scroll"><pre>{$trace}</pre></div>
END;
        } else {
            $trace = "";
        }

        print <<<END
$resetScript
<div style='text-align: center;'>
<h2 style='color: rgb(190, 50, 50);'>Exception Occurred:</h2>
<table style='width: 800px; display: inline-block;'>
<tr style='background-color:rgb(230,230,230);'><th style='width: 80px;'>Type</th><td>$exceptionType</td></tr>
<tr style='background-color:rgb(240,240,240);'><th>Message</th><td>{$e->getMessage()}</td></tr>
<tr style='background-color:rgb(230,230,230);'><th>File</th><td>{$e->getFile()}</td></tr>
<tr style='background-color:rgb(240,240,240);'><th>Line</th><td>{$e->getLine()}</td></tr>
</table>
{$trace}
END;
        if ($isFatal) {
            die();
        }
    }
    elseif ($isFatal)
    {
        if (!$headersSent) {
            header("HTTP/1.1 500 Internal Server Error");
            displayErrorPage() or die($resetScript);
            die();
        } else {
            die('An error occurred. Please try to refresh.');
        }
    }


    return true;
}

function displayErrorPage()
{
    global $SETTINGS;
    $url = isset($SETTINGS) ? $SETTINGS->rootUrl : basename(MAINDIR);
    include (__DIR__ . '/errorPage.php');
}

/**
 * Checks for a fatal error, work around for set_error_handler not working on fatal errors.
 */
function check_for_fatal()
{
    $error = error_get_last();
    if ( isFatal($error['type']))
        log_error( $error["type"], $error["message"], $error["file"], $error["line"] );
}

/**
 * Format an error message with the given parameters.
 * @param $message
 * @param $file
 * @param $line
 * @param string $column
 * @param bool|false $useHtml
 * @return string
 */
function generateLogMessage($message, $file, $line, $column = "", $useHtml = false)
{
    // Escape all inputs
    $filename = basename($file);
    if (is_numeric($line)) {
        $line = intval($line);
    }
    if (is_numeric($column)) {
        $column = intval($column);
        if ($useHtml) {
            $column = ", column <b>$column</b>";
        } else {
            $column = ", column $column";
        }
    }
    $date = date("Y-m-d H:i T");

    if ($useHtml) {
        // Create the entry
        return <<<ENT
[$date] $message in <b>$file</b> on line <b>$line</b>$column<br/>\n
ENT;
    } else {
        $spaces = str_repeat(' ', strlen($date) + 2);
        return <<<END
[$date] $message in $filename on line {$line}{$column}\n
END;
    }
}

/**
 * Check whether the error is fatal.
 * @param $type
 * @return bool
 */
function isFatal($type)
{
    static $fatalNums = [E_ERROR, E_USER_ERROR, E_COMPILE_ERROR, E_PARSE, E_CORE_ERROR, E_RECOVERABLE_ERROR];
    return in_array($type,  $fatalNums);
}