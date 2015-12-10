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
 * Limitation: Only one weekday can be in use
 * @author Thorben Dahl <thorben@sjostrom.no>
 * @copyright Thorben Dahl 2015
 * @license The MIT License (MIT)
 * @package tobinus\SimpleInfoscreen
 */
namespace tobinus\SimpleInfoscreen;

use \DateTime;
use \Exception;
require_once 'prepend.php';
require_once INCDIR . '/include.php';
use \tobinus\ErrorHandler as err;

$matchDates = new MatchDates();
$matchDateName = !empty($_GET['t']) ? $_GET['t'] : 'default';


// Set matchdate to first Sunday (or current day, if it's Sunday)
try {
    $matchDate = $matchDates->getDate($matchDateName);
} catch (\Exception $e) {
    // Given date was not found
    $matchDate = new DateTime('sunday');
    $matchDateName = 'default';
}
$matchDate->modify('midnight');

// One cachefile per setting, stale if it's from yesterday or config file was changed
$cacheFile = new Cache(
    "banedagbok_{$matchDateName}.html",
    max(
        filemtime(CONFIGDIR . '/matchdates.ini'),
        strtotime('today')
    )
);
try {
    $cacheFile->output();
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
$urlMatchDate = $matchDate->format('d.m.Y');
$url = "https://wp.nif.no/PageMatchAvansert.aspx?fromDate=${urlMatchDate}&toDate=${urlMatchDate}&venueId=2678&autosearch=true&showsearchpane=false&showinfopane=false&showpager=false&pagesize=500&design=4&fontsize=2&nourl=true&sfid=372&design=4";
//
// Download
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$extDocument = curl_exec($ch);

if (!$extDocument) {
    throw new \RuntimeException('Failed to fetch external document: ' . curl_error($ch));
}
curl_close($ch);
/* // Used when debugging/developing
$extDocument = file_get_contents(DATADIR . '/handballnedlasting.html');
$matchDate = new DateTime('sunday');*/

$document = new \DOMDocument();
libxml_use_internal_errors(true);
@$document->loadHTML($extDocument);
unset($extDocument);
$table = $document->getElementsByTagName('table')->item(0);

if (! $table instanceof \DOMElement) {throw new \RuntimeException('getElementsByTagName should return elements');}
$extTable = $table->getElementsByTagName('tr');
$matches = [];

// Enkel klasse som representerer en enkelt håndballkamp.
class Kamp
{
    public $starttid;
    public $hjemmelag;
    public $bortelag;
    public $avdeling;
    public $hjemmegarderobe;
    public $bortegarderobe;
}

// Foreach row in table
foreach ($extTable as $row) {
    if (! $row instanceof \DOMElement) {
        throw new \RuntimeException('DOMElement expected');
    }

    if ($row->hasAttribute('class') && $row->getAttribute('class') == 'info info-custom') {
        // This is the header, skip
        continue;
    }

    $rowTd = $row->getElementsByTagName('td');
    $rawTime = trim($rowTd->item(1)->textContent);

    $rowA = $row->getElementsByTagName('a');
    $rawTurnering = trim($rowA->item(1)->textContent);
    $rawHjemme = trim($rowA->item(2)->textContent);
    $rawBorte = trim($rowA->item(3)->textContent);

    $thisMatch = new Kamp();
    $thisMatch->starttid = DateTime::createFromFormat('H:i', $rawTime);
    $thisMatch->hjemmelag = $rawHjemme;
    $thisMatch->bortelag = $rawBorte;

    // Erstatt "divisjon" med "div"
    $division = mb_eregi_replace('divisjon', 'div', $rawTurnering);
    // Fjern informasjon om avdeling
    $division = mb_eregi_replace(' avd\.? \d+', '', $division);
    // Legg til mellomrom mellom kjønn og alder
    $division = mb_eregi_replace('([JG])(\d+)', '\1 \2', $division);
    $thisMatch->avdeling = $division;

    if (count($matches) % 2 == 0) {
        $thisMatch->hjemmegarderobe = 'A';
        $thisMatch->bortegarderobe = 'C';
    } else {
        $thisMatch->hjemmegarderobe = 'B';
        $thisMatch->bortegarderobe = 'D';
    }

    $matches[] = $thisMatch;
}


$twig = Template::init();

$output = $twig->render(
    'banedagbok.twig',
    [
        'kamper' => $matches,
        'dato' => $matchDate,
    ]);

$cacheFile->write($output);
$cacheFile->enableBrowserCaching();
echo $output;