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
 * Show what matches will be played today by parsing fotball.no
 *
 * @author Thorben Dahl <thorben@sjostrom.no>
 * @copyright Thorben Dahl 2015
 * @license The MIT License (MIT)
 * @package tobinus\SimpleInfoscreen
 */
namespace tobinus\SimpleInfoscreen;

use \DateTime;
require_once 'prepend.php';
require_once INCDIR . '/include.php';
use \tobinus\ErrorHandler as err;

// Stale if it's from yesterday
$cacheFile = new Cache(
    "kamper_fedrelandet.html",
    strtotime('00:06')
);
try {
    $cacheFile->output();
    die();
} catch (\RuntimeException $e) {
    // cache is not usable, generate…
}

$extDocCache = new Cache(
    'fotball_no.html',
    strtotime('00:05')
);

try {
    $extDocument = $extDocCache->get();
} catch (\RuntimeException $e) {
    // We must download the document

    // Download a list of matches from fotball.no
    if (!function_exists('curl_init')) {
        trigger_error('The cURL plugin is required in order to download match data from fotball.no', E_USER_ERROR);
        die();
    }

    $url = "http://www.fotball.no/fotballdata/Anlegg/Hjem/?fiksId=11978";

    // Download
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $extDocument = curl_exec($ch);

    if (!$extDocument) {
        throw new \RuntimeException('Failed to fetch external document: ' . curl_error($ch));
    }
    curl_close($ch);
    $extDocCache->write($extDocument);
}// */
 // Used when debugging/developing
//$extDocument = file_get_contents(DATADIR . '/fotballnedlasting.html');

 // Parse the document
$document = new \DOMDocument();
libxml_use_internal_errors(true);
@$document->loadHTML($extDocument);
$tables = $document->getElementsByTagName('table');
// get the right table
$eventTable = null;
if ($tables->length == 0) {
    trigger_error("No tables found on the external website", E_USER_ERROR);
}
foreach ($tables as $table) {
    if ($table instanceof \DOMElement && $table->hasAttribute('class') && $table->getAttribute('class') == 'eventTable') {
        // Are there any events?
        if ($table->childNodes->length === 0) {
            // Nope!
            $eventTable = [];
            break;
        }
        // Set eventTable to the list of rows in the second table body (thus skipping the headings)
        $tableBodies = $table->getElementsByTagName('tbody');
        if ($tableBodies->length == 0) {
            trigger_error('There are no table bodies in the event table on fotball.no - the page structure may have changed', E_USER_ERROR);
        }
        $eventTable = $tableBodies->item(0);
        if ($eventTable instanceof \DOMElement) {
            $eventTable = $eventTable->getElementsByTagName('tr');
        } else {
            trigger_error('Table was not DOMElement - PHP behaviour may have been changed', E_USER_ERROR);
        }

        break;
    }
}
if ($eventTable === null) {
    trigger_error("table with class eventTable was not found on the external website, the page structure may have changed", E_USER_ERROR);
    die();
}

// Enkel klasse som representerer en enkelt fotballkamp.
class Kamp
{
    public $starttid;
    public $hjemmelag;
    public $bortelag;
    public $avdeling;
    public $bane;
}

$matches = [];
$needles = [
    'Kunstgress',
    'Fedrelandet ',
    'Kg',
    'Er'
];
$replacements = [
    '11-er',
    '',
    '',
    '-er'
];
// Foreach row in table
foreach ($eventTable as $event) {
    if (!($event instanceof \DOMElement)) {
        trigger_error('Table row was not instance of DOMElement, PHP behaviour may have changed', E_USER_ERROR);
    }
    $timeNode = $event->getElementsByTagName('span')->item(0);
    $otherNodes = $event->getElementsByTagName('a');

    // Transform from array to Kamp object
    $thisMatch = new Kamp();

    $thisMatch->starttid = DateTime::createFromFormat(' H:i', $timeNode->textContent);
    $lagNode = mb_split(' - ', $otherNodes->item(0)->textContent);

    $thisMatch->hjemmelag = $lagNode[0];
    $thisMatch->bortelag = $lagNode[1];


    $thisMatch->avdeling = $otherNodes->item(1)->textContent;
    $thisMatch->avdeling = mb_ereg_replace(' avd \d+', '', $thisMatch->avdeling);
    $thisMatch->avdeling = mb_eregi_replace('([JG])(\d+)', '\1 \2', $thisMatch->avdeling);


    $thisMatch->bane = str_replace($needles, $replacements, mb_convert_case($otherNodes->item(2)->textContent, MB_CASE_TITLE));
    $matches[] = $thisMatch;
}

// Output
$twig = Template::init();

$output = $twig->render(
    'fotballkamper.twig',
    [
        'kamper' => $matches,
        'dato' => new DateTime('today'),
    ]);

$cacheFile->write($output);
$cacheFile->enableBrowserCaching();
echo $output;