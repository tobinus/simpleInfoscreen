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

    $url = "https://www.fotball.no/fotballdata/Anlegg/Hjem/?fiksId=11978";

    // Download
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    $extDocument = curl_exec($ch);

    if (!$extDocument) {
        throw new \RuntimeException('Failed to fetch external document: ' . curl_error($ch));
    }
    curl_close($ch);
    $extDocCache->write($extDocument);
}// */
 // Used when debugging/developing
//$extDocument = file_get_contents(DATADIR . '/fotballnedlasting.html');

// Were there any matches found?
if (!mb_ereg("Ingen kamper funnet", $extDocument)) {

    // Parse the document
    $document = new \DOMDocument();
    libxml_use_internal_errors(true);
    @$document->loadHTML($extDocument);
    $div_match = $document->getElementById('matches');
    if ($div_match === null) {
        trigger_error('No element with ID "matches" was found on fotball.no - the page structure may have changed', E_USER_ERROR);
    }
    $table = $div_match->getElementsByTagName('table')->item(0);
    if ($table !== null && $table instanceof \DOMElement) {
        // Set eventTable to the list of rows in the second table body (thus skipping the headings)
        $tableBodies = $table->getElementsByTagName('tbody');
        if ($tableBodies->length == 0) {
            trigger_error('There are no table bodies in the matches table on fotball.no - the page structure may have changed', E_USER_ERROR);
        }
        $eventTable = $tableBodies->item(0);
        if ($eventTable instanceof \DOMElement) {
            $eventTable = $eventTable->getElementsByTagName('tr');
        } else {
            trigger_error('Tablebody was not DOMElement - PHP behaviour may have been changed', E_USER_ERROR);
        }
    } else {
        trigger_error("'#matches table' was not found on the external website, the page structure may have changed", E_USER_ERROR);
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
        $timeNode = $event->getElementsByTagName('td')->item(2);
        $otherNodes = $event->getElementsByTagName('a');

        // Transform from array to Kamp object
        $thisMatch = new Kamp();

        $thisMatch->starttid = DateTime::createFromFormat('H:i', $timeNode->textContent);

        $thisMatch->hjemmelag = $otherNodes->item(0)->textContent;
        $thisMatch->bortelag = $otherNodes->item(2)->textContent;


        $thisMatch->avdeling = $otherNodes->item(4)->textContent;
        $thisMatch->avdeling = mb_ereg_replace(' avd \d+', '', $thisMatch->avdeling);
        $thisMatch->avdeling = mb_eregi_replace('([JG])(\d+)', '\1 \2', $thisMatch->avdeling);


        $thisMatch->bane = str_replace($needles, $replacements, mb_convert_case($otherNodes->item(3)->textContent, MB_CASE_TITLE));
        $matches[] = $thisMatch;
    }
} else {
    // No matches
    $matches = array();
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