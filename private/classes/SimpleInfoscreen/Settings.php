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
 * @file Class for parsing the infoscreen.ini file and access its options.
 * @author Thorben Werner Sjøstrøm Dahl <thorben@sjostrom.no>
 * @copyright Thorben Werner Sjøstrøm Dahl 2015
 * @license http://opensource.org/licenses/MIT The MIT License
 * @package tobinus\SimpleInfoscreen
 */

namespace tobinus\SimpleInfoscreen;

/**
 * Loads all the settings from infoscreen.ini.
 *
 * Property names, with one exception, correspond to the name in the config file,
 * but without the section name. (applicationURL can be accessed as $rootUrl)
 * @package tobinus\SimpleInfoscreen
 * @property-read bool $enableScheduling [infoscreen] true if enabled, false otherwise
 * @property-read string $defaultSlideShow [infoscreen] slideShowToUse[default]
 * @property-read string $weekdaySlideShow [infoscreen] slideShowToUse[weekday]
 * @property-read string $weekendSlideShow [infoscreen] slideShowToUse[weekend]
 * @property-read array $dayOfWeekSlideShows [infoscreen] slideShowToUse[mon-sun]
 * @property-read int $secondsBetweenUpdateChecks [infoscreen] Amount of seconds between AJAX
 * requests to updateAvailable.php.
 * @property-read int $slideLoadTimeout [infoscreen] seconds between initializing
 * a page load, and cancelling the loading of that page (and skipping it).
 * @property-read string $title [infoscreen] name to use in <title>
 * @property-read string $transition [appearance] use <$transition>.transitions.SlideShow.js
 * for transitions between slides.
 * @property-read bool $useProgressBar [appearance] show and animate progress bar
 * if true, do not use it when false.
 * @property-read bool $showSlideNumber [appearance] show the current slide number
 * and slide total (eg. 1/3)
 * @property-read bool $enableJSErrorReporting [server] whether to log error messages from JS
 * @property-read string $rootUrl [server] applicationRoot[url] full URL to the
 * folder in which the application resides, as seen by users and their agents.
 * @property-read bool $requireSSL [server] true if the user MUST connect using https.
 * Any connection made over unsecure http must result in a redirect to the same
 * page, except the url starts with https://.
 */
class Settings extends AbstractSettings
{
    /**
     * @internal The version of this program. Increment when a configuration file
     * for the previous version won't be compatible with this version.
     */
    const version = 0.1;

    protected $CONFIG_FILE;
    /**#@+
     * @internal Fields used to store the settings. Made protected so that they
     * cannot be written to (since this class is read-only as of now). They are
     * accessed through {@link __get()}, which lets users access the fields specified
     * in {@link $accessibleFields}.
     * @access private
     */
    protected $enableScheduling;
    protected $defaultSlideShow;
    protected $weekdaySlideShow;
    protected $weekendSlideShow;
    protected $dayOfWeekSlideShows;
    protected $secondsBetweenUpdateChecks;
    protected $slideLoadTimeout;
    protected $title;
    protected $transition;
    protected $useProgressBar;
    protected $showSlideNumber;
    protected $rootUrl;
    protected $requireSSL;
    protected $enableJSErrorReporting;
    /**#@-*/

    protected $configFileLoaded = false;

    /**
     * Prepare for reading the settings, or optionally read them now.
     * @param bool $forceLoading Set to true to load the configuration file now.
     */
    public function __construct($forceLoading = false)
    {
        $this->CONFIG_FILE = CONFIGDIR . '/settings.ini';
        if ($forceLoading)
        {
            $this->populateFromConfigFile();
        }
    }

    /**
     * Parse the INI file, use defaults when an option is not found in the file,
     * and store those options in the object.
     */
    public function populateFromConfigFile()
    {
        $settings = $this->readFromConfigFile();

        // Give default values to all options not present in the ini file
        // [server]
        set_unless_defined($settings['server'], array());
        $server = $settings['server'];

        set_unless_defined($server['enableJSErrorReporting'], false);

        if (!isset($server['applicationURL'])) {
            trigger_error("applicationURL must be provided in infoscreen.ini", E_USER_ERROR);
        }

        set_unless_defined($server['requireSSL'], false);

        // [infoscreen]
        set_unless_defined($settings['default'], array());
        $infoscreen = $settings['default'];

        set_unless_defined($infoscreen['enableScheduling'], true);

        set_unless_defined($infoscreen['slideShowToUse'], array());
        $slideShowToUse = $infoscreen['slideShowToUse'];
        set_unless_defined($slideShowToUse['default'], 'default');
        set_unless_defined($slideShowToUse['weekday'], 'weekday');
        set_unless_defined($slideShowToUse['weekend'], 'weekend');

        set_unless_defined($infoscreen['secondsBetweenUpdateChecks'], 60);
        set_unless_defined($infoscreen['slideLoadTimeout'], 5);
        set_unless_defined($infoscreen['title'], 'Infoscreen');

        $appearance = $infoscreen;

        set_unless_defined($appearance['transition'], 'slideUp');
        set_unless_defined($appearance['useProgressBar'],
            ($appearance['useProgressBar'] == 'cut') ? false : true);
        set_unless_defined($appearance['showSlideNumber'], true);

        // Actually assign those options to this object
        $this->enableScheduling = $infoscreen['enableScheduling'];
        $this->secondsBetweenUpdateChecks = $infoscreen['secondsBetweenUpdateChecks'];
        $this->slideLoadTimeout = $infoscreen['slideLoadTimeout'];
        $this->defaultSlideShow = $slideShowToUse['default'];
        $this->weekdaySlideShow = $slideShowToUse['weekday'];
        $this->weekendSlideShow = $slideShowToUse['weekend'];
        $this->dayOfWeekSlideShows = array_diff_key($slideShowToUse, array_flip(['default', 'weekday', 'weekend']));
        $this->title = $infoscreen['title'];
        $this->transition = $appearance['transition'];
        $this->useProgressBar = $appearance['useProgressBar'];
        $this->showSlideNumber = $appearance['showSlideNumber'];
        $this->rootUrl = $server['applicationURL'];
        $this->requireSSL = $server['requireSSL'];
        $this->enableJSErrorReporting = $server['enableJSErrorReporting'];

        $this->configFileLoaded = true;
    }

    /**
     * @var array These fields can be accessed as read-only through the magic
     * method {@link __get()}.
     */
    protected $accessibleFields =
        [
            'rootUrl',
            'enableJSErrorReporting',
            'requireSSL',
            'enableScheduling',
            'defaultSlideShow',
            'weekdaySlideShow',
            'weekendSlideShow',
            'dayOfWeekSlideShows',
            'secondsBetweenUpdateChecks',
            'slideLoadTimeout',
            'title',
            'transition',
            'useProgressBar',
            'showSlideNumber',
        ];

    /**
     * @inheritdoc
     */
    protected function getAccessibleFields()
    {
        return $this->accessibleFields;
    }

    /**
     * @var array See {@link getExpectations()}
     * @see getExpectations()
     */
    protected $expectations =
        [
            'version' => 'float',
            'server' =>
                [
                    'applicationURL' => 'string',
                    'enableJSErrorReporting' => 'bool',
                    'requireSSL' => 'bool',
                ],
            'default' =>
                [
                    'enableScheduling' => 'bool',
                    'slideShowToUse' => 'arrayKeys [default weekday weekend mon tue wed thu fri sat sun]',
                    'secondsBetweenUpdateChecks' => 'int',
                    'slideLoadTimeout' => 'int',
                    'title' => 'string',
                    'transition' => ['cut', 'fade', 'slideUp', 'slideLeft'],
                    'useProgressBar' => 'bool',
                    'showSlideNumber' => 'bool',
                ]
        ];


    /**
     * Returns the filename of the .ini-file to be used.
     * @return string {string} path/to/file.ini, relative to the application root
     * path/to/file.ini, relative to the application root
     */
    protected function getConfigFilename()
    {
        return $this->CONFIG_FILE;
    }

    /**
     * @inheritdoc
     */
    protected function getExpectations()
    {
        return $this->expectations;
    }

    /**
     * Returns the newest version number for this program.
     * Used when deciding compatibility.
     * @return string {string} string Newest version number.
     */
    protected function getNewestVersionNumber()
    {
        return self::version;
    }

    /**
     * Dummy implementation, returns $settings.
     * @source
     * @inheritdoc
     * @override
     */
    protected function makeOldVersionCompatible($settings)
    {
        return $settings;
    }

    /**
     * Custom method for validating the options for which slideshows should be
     * used.
     * @param mixed $value The value accosiated with the "slideShowToUse" option.
     * @param null $expectationOptions Not used.
     * @param string $key The name of this option entry (usually slideShowToUse)
     * @return array The validated $value, with only valid keys and entries.
     * @throws \InvalidArgumentException when validation fails.
     */
    protected function validateSlideShows($value, $expectationOptions, $key)
    {
        print('validateSlideShows was called');
        if (!is_array($value)) {
            throw new \InvalidArgumentException('expected array, string was received');
        }
        $expectedKeys = array_flip(['default', 'weekday', 'weekend']);
        // Create array with all 'required' keys that are not found in $value
        $missingKeys = array_diff_key(
            $expectedKeys,
            $value
        );
        // Create array with all $value keys that weren't recognized
        $unrecognizedKeys = array_diff_key(
            $value,
            $expectedKeys
        );
        $numMissingKeys = count($missingKeys);
        $numUnrecognizedKeys = count($unrecognizedKeys);

        $file = $this->getConfigFilename();

        $errorStart = " Config file $file, section infoscreen:";
        if ($numMissingKeys > 0 && $numUnrecognizedKeys > 0) {
            trigger_error("$errorStart Option entries {$key}[".implode("], {$key}[", array_keys($expectedKeys)).
            "] were expected, but not found. Additionally, the following entries were".
            " found but not recognized: {$key}[".implode("], {$key}[", array_keys($missingKeys)).
            "]. Perhaps some of them were misspelled? ");
        } elseif($numMissingKeys > 0) {
            trigger_error("$errorStart Option entries {$key}[".implode("], {$key}[", array_keys($missingKeys)).
            "] were expected, but not found. Defaults used instead");
        } elseif($numUnrecognizedKeys > 0) {
            trigger_error("$errorStart The following entries were found, but not recognized: ".
            "{$key}[" . implode("], {$key}[", array_keys($unrecognizedKeys)) . "].".
            " This likely indicates that the config file is aiming at a newer version".
            " which regonizes those entries. Perhaps upgrade?");
        }

        return array_intersect_key($value, $expectedKeys);
    }

    /**
     * Load the infoscreen specific settings, overwriting the default settings
     * @param string $infoscreen Name of infoscreen settings to look for.
     */
    public function loadInfoscreen($infoscreen)
    {
        if (!$this->configFileLoaded) {
            $this->populateFromConfigFile();
        }
        // Load from file
        $newSettings = $this->convertIniToArray(CONFIGDIR . '/infoscreens.ini');
        // We are expecting a section with the name given, in the same format as infoscreen section
        $newSettings = $this->validateOptions($newSettings, [
            'version' => 'float',
            $infoscreen => $this->expectations['default'],
        ], 'infoscreens.ini', false);
        // Overwrite the existing settings, but only if new setting is present
        if (isset($newSettings[$infoscreen])) {
            $infoSettings = $newSettings[$infoscreen];
            change($this->enableScheduling, $infoSettings['enableScheduling']);
            if (isset($infoSettings['slideShowToUse'])) {
                change($this->defaultSlideShow, $infoSettings['slideShowToUse']['default']);
                change($this->weekdaySlideShow, $infoSettings['slideShowToUse']['weekday']);
                change($this->weekendSlideShow, $infoSettings['slideShowToUse']['weekend']);
                foreach (['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'] as $day) {
                    change($this->dayOfWeekSlideShows[$day], $infoSettings['slideShowToUse'][$day]);
                }
            }
            change($this->secondsBetweenUpdateChecks, $infoSettings['secondsBetweenUpdateChecks']);
            change($this->slideLoadTimeout, $infoSettings['slideLoadTimeout']);
            change($this->title, $infoSettings['title']);
            change($this->transition, $infoSettings['transition']);
            change($this->useProgressBar, $infoSettings['useProgressBar']);
            change($this->showSlideNumber, $infoSettings['showSlideNumber']);
        } else {
            throw new \RuntimeException("$infoscreen was not found in infoscreens.ini");
        }
    }
}

/**
 * Helper function which assigns source to target, but only if source is set
 * @param mixed $target Variable to be overwritten.
 * @param mixed $source Variable which will be assigned to target, but only if it is not empty.
 */
function change(&$target, &$source)
{
    if (isset($source)) {
        $target = $source;
    }
}