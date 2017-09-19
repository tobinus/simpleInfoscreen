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
 * Show what matches will be played today (or any given day), by parsing a webpage
 * from handball.no.
 *
 * @author Thorben Dahl <thorben@sjostrom.no>
 * @copyright Thorben Dahl 2016
 * @license The MIT License (MIT)
 * @package tobinus\SimpleInfoscreen
 */
namespace tobinus\SimpleInfoscreen;

use \DateTime;
use \DateTimeZone;
use \Exception;
require_once 'prepend.php';
require_once INCDIR . '/include.php';
use \tobinus\ErrorHandler as err;
$matchDates = new MatchDates();
$matchDateName = !empty($_GET['t']) ? $_GET['t'] : 'default';


// Look up in configuration file for the correct date
try {
    $matchDateString = $matchDates->getDate($matchDateName);
} catch (\Exception $e) {
    // Given date was not found
    $matchDateName = 'default';
    $matchDateString = 'sunday';
}
$matchDate = new DateTime($matchDateString);

// Set the time to 00:00 (because we only care about the date)
$matchDate->modify('midnight');

// One cachefile per setting, cache is validated if it's newer than last configuration change and last friday (to catch any changes)
$cacheFile = new Cache(
    "banedagbok_{$matchDateName}.html",
    max(
        filemtime(CONFIGDIR . '/matchdates.ini'),
        strtotime("-1 week friday"),
        filemtime(__FILE__)
    )
);
try {
    // Perform an additional check on the cache validity
    // The cache is invalid if the resulting date from new DateTime() is different now than it was when the cache was created
    // What was the evaluated date back when the cache was created?
    $cacheTime = "@" . ($cacheFile->getModificationTime() - 15);  // get cache creation time, subtract 15 just to be sure we're not using an old cache
    $oldMatchDate = new DateTime($cacheTime);  // start with the cache time
    $oldMatchDate->setTimeZone(new DateTimeZone("Europe/Oslo"));
    $oldMatchDate->modify($matchDateString)->modify("midnight");  // apply the match date to the cache date, and set to midnight
    // Is there a difference?
    if ($matchDate != $oldMatchDate) {
        // Yes
        throw new StaleCache();
    }
    // Try to use the cache
    $cacheFile->output();
    // Cache was used successfully
    die();
} catch (\RuntimeException $e) {
    // cache is not usable, generate…
}

// Download a list of matches from handball.no
if (!function_exists('curl_init')) {
    trigger_error('The cURL plugin is required in order to download match data from handball.no', E_USER_ERROR);
    die();
}

// Generate part of URL
$urlMatchDate = $matchDate->format('m.d.Y');
$url = "https://www.handball.no/AjaxData/SortedMatchesReservationsForVenue?fom={$urlMatchDate}&tom={$urlMatchDate}&id=4080";
// Download
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$extDocument = curl_exec($ch);
$extStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
if (!$extDocument || $extStatusCode != 200) {
    throw new \RuntimeException('Failed to fetch external document: ' . curl_error($ch));
}
curl_close($ch);
/* // Used when debugging/developing
$extDocument = file_get_contents(DATADIR . '/handballnedlasting.html');
$matchDate = new DateTime('sunday');*/

$matches = [];

// Are there any matches?
if (!mb_ereg("ingen kamper", $extDocument))  {
    $document = new \DOMDocument();
    libxml_use_internal_errors(true);
    @$document->loadHTML($extDocument);
    unset($extDocument);  // external document might be large, so release memory explicitly
    $table = $document->getElementsByTagName('table')->item(0)->getElementsByTagName('tbody')->item(0);

    if (!$table instanceof \DOMElement) {
        throw new \RuntimeException('Element not found, has the website changed?');
    }
    $extTable = $table->getElementsByTagName('tr');

// Enkel klasse som representerer en enkelt håndballkamp.
    class Kamp
    {
        public $starttid;
        public $sluttid;
        public $hjemmelag;
        public $bortelag;
        public $avdeling;
        public $hjemmegarderobe;
        public $bortegarderobe;
    }

// Foreach row in table
    foreach ($extTable as $row) {
        if (!$row instanceof \DOMElement) {
            throw new \RuntimeException('DOMElement expected');
        }

        $rowTd = $row->getElementsByTagName('td');
        $rawTime = mb_split("-", trim($rowTd->item(2)->textContent));

        $rowA = $row->getElementsByTagName('a');
        $rawTurnering = trim($rowA->item(0)->textContent);
        $rawHjemme = trim($rowA->item(3)->textContent);
        $rawBorte = trim($rowA->item(4)->textContent);

        $thisMatch = new Kamp();
        $thisMatch->starttid = DateTime::createFromFormat('H:i', $rawTime[0]);
        $thisMatch->sluttid = DateTime::createFromFormat('H:i', $rawTime[1]);
        $thisMatch->hjemmelag = $rawHjemme;
        $thisMatch->bortelag = $rawBorte;

        // Erstatt "divisjon" med "div"
        $division = mb_eregi_replace('divisjon', 'div', $rawTurnering);
        // Fjern informasjon om avdeling
        $division = mb_eregi_replace(' avd\.? \d+', '', $division);
        $thisMatch->avdeling = $division;

        if (count($matches) % 2 == 0) {
            $thisMatch->hjemmegarderobe = 'A';
            $thisMatch->bortegarderobe = 'B';
        } else {
            $thisMatch->hjemmegarderobe = 'C';
            $thisMatch->bortegarderobe = 'D';
        }

        $matches[] = $thisMatch;
    }
}

$twig = Template::init();

$output = $twig->render(
    'banedagbok.twig',
    [
        'kamper' => $matches,
        'dato' => $matchDate,
    ]);

$cacheFile->writeAndOutput($output);
