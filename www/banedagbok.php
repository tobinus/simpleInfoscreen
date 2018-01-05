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
use \DateTimeImmutable;
use \DateInterval;
use \Exception;
require_once 'prepend.php';
require_once INCDIR . '/include.php';
use \tobinus\ErrorHandler as err;

$DEFAULT_MATCH_DURATION = DateInterval::createFromDateString('1 hour 15 minutes');

// Which venue should we use?
/* Key is what the v GET parameter is matched against. The value is an array with the fields:
     title: Text shown as title
     id: venueId from nif.no
     displayChangingRooms: true to display, false to hide, 'auto' to show when the rooms are assigned successfully.
     changingRooms: list of changing rooms to use when using simple scheduling (done when only one field is used)
     displayField: true to display, false to hide, 'auto' to show when there are multiple fields in use
     displaySport: true to display, false to hide, 'auto' to show when there are multiple sports
*/
$venues = array(
    'fethallen' => array(
        'title' => 'Kamper i Fethallen',
        'id' => 4080,
        'displayChangingRooms' => 'auto',
        'changingRooms' => ['A', 'B', 'C', 'D'],
        'displayField' => 'auto',
        'displaySport' => true,
    ),
    'eika' => array(
        'title' => 'Kamper i Eika Fet Arena',
        'id' => 33607,
        'displayChangingRooms' => true,
        'changingRooms' => ['A3', 'A4', 'B3', 'B4'],
        'displayField' => true,
        'displaySport' => true,
    ),
);
define('DEFAULT_VENUE', 'fethallen');
$chosenVenueName = !empty($_GET['v']) ? $_GET['v'] : DEFAULT_VENUE;
if (!array_key_exists($chosenVenueName, $venues)) {
    http_response_code(404);
    die('<h1>404 Not Found</h1><p>Venue name not recognized</p>');
}
$chosenVenueInfo = $venues[$chosenVenueName];

// Which date?
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

// One cachefile per setting per venue, cache is validated if it's newer than last configuration change and last friday (to catch any changes)
$cacheFile = new Cache(
    "banedagbok_{$chosenVenueName}_{$matchDateName}.html",
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

// Download a list of matches from nif.no
if (!function_exists('curl_init')) {
    trigger_error('The cURL plugin is required in order to download match data from nif.no', E_USER_ERROR);
    die();
}

// Generate part of URL
$urlMatchDate = $matchDate->format('d.m.Y');
$url = "https://wp.nif.no/PageMatchAvansert.aspx?venueUnitId={$chosenVenueInfo['id']}&FromDate={$urlMatchDate}&ToDate={$urlMatchDate}&showJsonData=true&autoSearch=true";
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

$schedule = json_decode($extDocument, true);
unset($extDocument);  // external document might be large, so release memory explicitly

$matches = [];
$lastMatchPerField = [];
$changingRoomIndex = 0;

// Enkel klasse som representerer en enkelt håndballkamp.
class Kamp
{
    public $starttid;
    public $sluttid;
    public $hjemmelag;
    public $bortelag;
    public $avdeling;
    public $bane;
    public $sport;
    public $hjemmegarderobe;
    public $bortegarderobe;
}

function prepareTeamName($rawName) {
    $rawName = htmlspecialchars($rawName, ENT_QUOTES | ENT_HTML5, "UTF-8");
    $rawName = trim($rawName);
    $rawName = mb_eregi_replace(' ', '&nbsp;', $rawName);
    return $rawName;
}

foreach ($schedule as $row) {
    $thisMatch = new Kamp();
    $thisMatch->starttid = DateTimeImmutable::createFromFormat('H:i', $row['Tid']);
    $thisMatch->hjemmelag = prepareTeamName($row['Hjemmelag']['Text']);
    $thisMatch->bortelag = prepareTeamName($row['Bortelag']['Text']);

    $rawTurnering = $row['Turnering']['Text'];
    // Erstatt "divisjon" med "div"
    $division = mb_eregi_replace('divisjon', 'div', $rawTurnering);
    // Erstatt "veteran" med "vet."
    $division = mb_eregi_replace('veteran', 'vet.', $division);
    // Fjern informasjon om avdeling
    $division = mb_eregi_replace('(?: -)? (?:avd(?:\.|eling|)) ?\S+', '', $division);
    // Forkort "Gutter" og "Jenter"
    $division = mb_eregi_replace('(G)utter |(J)enter ', '\1\2', $division);
    $division = htmlspecialchars($division, ENT_QUOTES | ENT_HTML5, "UTF-8");
    $division = mb_eregi_replace('([^-])serien', '\1&shy;serien', $division);
    $thisMatch->avdeling = $division;

    $rawSport = mb_eregi_replace('Fet IL - (\S+).*', '\1', $row['Arrangør']);
    if (mb_eregi('Håndball', $rawSport)) {
        $thisMatch->sport = 'handball';
    } elseif (mb_eregi('Innebandy', $rawSport)) {
        $thisMatch->sport = 'floorball';
    } else {
        $thisMatch->sport = null;
    }

    $rawBane = $row['Bane']['Text'];
    $splittedBane = explode(' ', $rawBane);
    $thisMatch->bane = array_slice($splittedBane, -1, 1)[0];

    if ($chosenVenueInfo['displayChangingRooms']) {
        $changingRooms = $chosenVenueInfo['changingRooms'];
        $thisMatch->hjemmegarderobe = $changingRooms[$changingRoomIndex];

        $changingRoomIndex++;
        $changingRoomIndex = $changingRoomIndex % count($changingRooms);

        $thisMatch->bortegarderobe = $changingRooms[$changingRoomIndex];

        $changingRoomIndex++;
        $changingRoomIndex = $changingRoomIndex % count($changingRooms);
    }

    // Update last match's end time to be our end time
    if (array_key_exists($thisMatch->bane, $lastMatchPerField)) {
        $maxEndTime = $lastMatchPerField[$thisMatch->bane]->starttid->add($DEFAULT_MATCH_DURATION);
        $lastMatchPerField[$thisMatch->bane]->sluttid = min($maxEndTime, $thisMatch->starttid);
    }
    $lastMatchPerField[$thisMatch->bane] = $thisMatch;

    $matches[] = $thisMatch;
}

// Use default duration for the last matches, where we cannot find their
// duration based on the next match
foreach ($lastMatchPerField as $field => $match) {
    $match->sluttid = $match->starttid->add($DEFAULT_MATCH_DURATION);
}

// Calculate auto values for venues
$fields = [];
$sports = [];
foreach ($matches as $match) {
    $fields[$match->bane] = true;
    $sports[$match->sport] = true;
}
$numFields = count($fields);
$numSports = count($sports);

$displayChangingRooms = $chosenVenueInfo['displayChangingRooms'];
if ($displayChangingRooms === 'auto') {
    $displayChangingRooms = ($numFields == 1);
}

$displayField = $chosenVenueInfo['displayField'];
if ($displayField === 'auto') {
    $displayField = ($numFields != 1);
}

$displaySport = $chosenVenueInfo['displaySport'];
if ($displaySport === 'auto') {
    $displaySport = ($numSports != 1);
}

// Shall we trust the result of the simple changing room algorithm?
if ($numFields > 1 && $displayChangingRooms) {
    // Nope, set all changing rooms to placeholder
    foreach ($matches as $match) {
        $match->hjemmegarderobe = '‒';
        $match->bortegarderobe = '‒';
    }
}

$twig = Template::init();

$output = $twig->render(
    'banedagbok.twig',
    [
        'kamper' => $matches,
        'dato' => $matchDate,
        'title' => $chosenVenueInfo['title'],
        'displayChangingRooms' => $displayChangingRooms,
        'displayField' => $displayField,
        'displaySport' => $displaySport,
    ]);

$cacheFile->writeAndOutput($output);
