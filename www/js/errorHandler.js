/**
 * Created by thorben on 20.07.15.
 */

var hasStartedErrorTimeout = false;
var reportedErrors = [];

function reloadIfError()
{
    if (!hasStartedErrorTimeout) {
        setTimeout(function () {location.reload(true)}, 60 * 1000);
        console.info("The page will be reloaded in 1 minute due to JavaScript error.");
        hasStartedErrorTimeout = true;
    }
    return false;
}

/**
 *
 * @param msg string
 * @param url
 * @param line
 * @param column
 */
function reportError(msg, url, line, column)
{
    if (reportedErrors.indexOf(msg) == -1 ) {
        reportedErrors.push(msg);
        reloadIfError();
        $.ajax( {
            url: 'reportError.php',
            type: 'POST',
            data: {
                message: msg,
                url: url,
                line: line,
                column: column
            },
            success: function ()
            {
                console.info( msg.split( ':' )[0] + " was reported successfully to the server." );
            },
            error: function (xhr, status, errorThrown)
            {
                if (errorThrown == 'Forbidden') {
                    console.info('Logging of JavaScript errors is disabled on the server.');
                } else
                {
                    console.error( "Error when reporting error: " + errorThrown + ", status: " + status );
                    console.dir( xhr );
                }
            }
        });
    }
}

window.onerror = reportError;