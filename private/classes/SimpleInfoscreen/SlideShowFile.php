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
 * @file Class for parsing the slideshows.ini file and access the slideshows
 * described there.
 * @author Thorben Werner Sjøstrøm Dahl <thorben@sjostrom.no>
 * @copyright Thorben Werner Sjøstrøm Dahl 2015
 * @license http://opensource.org/licenses/MIT The MIT License
 * @package tobinus\SimpleInfoscreen
 */

namespace tobinus\SimpleInfoscreen;


use Traversable;

/**
 * Parses a configuration file containing descriptions of SlideShows.
 *
 * @package tobinus\SimpleInfoscreen
 */
class SlideShowFile extends AbstractSettings implements \IteratorAggregate
{

    /**
     * @var array $slideShows Associative array with the slideshows from the file.
     */
    protected $slideShows;

    /**
     * @var string $configFile Pathname of the configuration file to use.
     */
    protected $configFile;

    /**
     * @var bool $useFile Whether file-related actions are activated or not.
     */
    protected $useFile;

    /**
     * @var array $slides Associative array with slidename as key, Slide object
     * as value. Used when slides reference back to earlier slides.
     */
    protected $slides;


    /**
     * This class should be constructed using {@link SlideShowFile::open} or {@link SlideShowFile::create}.
     */
    protected function __construct()
    {

    }

    /**
     * Creates a new instance of SlideShowFile, loading the slideshows using
     * the specified configuration file.
     * @param $filename string Absolute path to the configuration file (use CONFIGDIR)
     * @param bool|false $forceInit Load and populate the fields now if true,
     * or when needed when false (lazy loading).
     * @return SlideShowFile An instance with the slideshows from $filename.
     */
    public static function open($filename, $forceInit = false)
    {
        $newSlideShow = new self();

        if (is_readable($filename))
        {
            $newSlideShow->configFile = $filename;
            $newSlideShow->useFile = true;
        } else
        {
            throw new \InvalidArgumentException('Supplied config file <b>' . $filename .'</b> does not exist or it cannot be read');
        }

        if ($forceInit)
        {
            $newSlideShow->populateFromConfigFile();
        }

        return $newSlideShow;
    }

    /**
     * Create a new SlideShowFile instance with only the given slideshows.
     * @param $slideShows SlideShow|array|null Slideshows to add.
     */
    public static function create($slideShows = null)
    {
        $newSlideShow = new self();
        $newSlideShow->useFile = false;

        if ($slideShows !== null) {
            $newSlideShow->addSlideShow($slideShows);
        }
    }

    /**
     * MAIN - Populates this instance with setting values from config file.
     * Called as soon as the options are needed (lazy loading).
     * @return void
     */
    protected function populateFromConfigFile()
    {
        if (!$this->configFile) {
            throw new \LogicException('populateFromConfigFile was called, but there is no config file configured.');
        }

        $settings = $this->readFromConfigFile();

        $this->slideShows = array_diff_key($settings, ['version' => null]);
    }

    /**
     * Returns the filename of the .ini-file to be used.
     * @return string {string} path/to/file.ini, relative to the application root
     */
    protected function getConfigFilename()
    {
        return $this->configFile;
    }

    /**
     * Not used, since SlideShowFile overrides the stock validateOptions method.
     * @inheritdoc
     */
    protected function getExpectations()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    protected function validateOptions($settings, $expectations, $filename, $checkUnused = true)
    {
        $newSettings = [];
        foreach ($settings as $key => $value) {
            if ($key == 'version') {
                $newSettings['version'] = $value;
                continue;
            }
            try {
                $newSettings[$key] = $this->validateSlideShow($value, null, $key);
            } catch (\InvalidArgumentException $e) {
                // Value cannot be used.
                trigger_error("Configuration error in file <b>$filename</b>, " .
                    "slideshow named <b>$key</b>: " .
                    $e->getMessage() . ". That slideshow cannot be loaded", E_USER_WARNING);
            }
        }
        return $newSettings;
    }

    /**
     * Makes a SlideShow out of the given array (that is, section in config file)
     * @param $value array Section from the config file
     * @param $otherExpectations null not used
     * @param string $key The name of this section (used as SlideShow ID)
     * @return SlideShow The SlideShow described in the given array.
     */
    protected function validateSlideShow($value, $otherExpectations, $key = "")
    {
        if (!is_array($value))
        {
            throw new \InvalidArgumentException(
                "This ain't a slideshow! (Use [slideShowId] before listing ".
                "slides.. or upgrade the application if this option was".
                "supposed to be recognized)"
            );
        }

        $id = $key;

        if (!empty($value['name'])) {
            $name = $value['name'];
        } else {
            $name = $id;
        }

        $slideShow = new SlideShow($id, $name);

        foreach ($value as $slideName => $slide) {
            if ($slideName == 'name' || $slideName == 'id') {
                continue;
            }
            try {
                $slideShow->addSlide($this->validateSlide($slide, $slideName));
            } catch (\InvalidArgumentException $e) {
                trigger_error($e->getMessage() . ", in file <b>".$this->getConfigFilename()
                . "</b>, slideshow <b>$name</b> (<b>$id</b>), slide ".
                "<b>$slideName</b>. The slide will be skipped", E_USER_NOTICE);
            }
        }

        return $slideShow;
    }

    /**
     * Make a Slide out of the given array, or throw \InvalidArgumentException.
     * @param $value array|string A slide entry in the file.
     * @param $key string Slide name.
     * @return Slide if the array cannot be made into a Slide.
     */
    protected function validateSlide($value, $key)
    {
        // Is this a proper entry?
        if (!is_array($value))
        {
            // Perhaps it's referencing an earlier slide?
            if (array_key_exists($value, $this->slides)) {
                $newSlide = $this->slides[$value];
                $this->slides[$key] = $newSlide;
                return $newSlide;
            }
            throw new \InvalidArgumentException("$key tries to reference back to $value, which was not found");
        }
        if (empty($value['url']))
        {
            throw new \InvalidArgumentException("$key does not have a url, is 'url' or the slide name misspelled?");
        }

        static $keys = ['url', 'duration', 'loadingTime'];
        static $friendlyDebug = true;

        if ($friendlyDebug && count($difference = array_diff(array_keys($value), $keys))) {
            throw new \InvalidArgumentException("The following entries were not recognized, perhaps they are misspelled: ".
            json_encode($difference));
        }
        set_unless_defined($value['duration'], 10);
        set_unless_defined($value['loadingTime'], 5);

        $url = $value['url'];
        $duration = intval($value['duration']);
        $loadingTime = intval($value['loadingTime']);

        $newSlide = new Slide($url, $duration, $loadingTime);
        $this->slides[$key] = $newSlide;
        return $newSlide;
    }

    /**
     * Returns the newest version number for this program.
     * Used when deciding compatibility.
     * @return string {string} Newest version number.
     */
    protected function getNewestVersionNumber()
    {
        return '0.1';
    }

    /**
     * Not used, since getters and setters for slideshow are implemented instead
     * of relying on __get().
     * @return array Array with strings that name the fields that others can access,
     * but not set.
     */
    protected function getAccessibleFields()
    {
        return [];
    }

    /**
     * Get the slideshow specified by the $id.
     * @param $id string Id of the slideshow to get, as specified by the section
     * name preceding the slides.
     * @return SlideShow The slideshow with the id $id.
     */
    public function getSlideShow($id)
    {
        try {
            $this->checkAvail($this->slideShows);
            $this->checkAvail($this->slideShows[$id]);

            return $this->slideShows[$id];
        } catch (\RuntimeException $e) {
            trigger_error("There is no slideshow named $id in {$this->configFile}.".
            " Please make sure the name in infoscreen.ini (section infoscreen, ".
                "option slideShowToUse) and {$this->configFile} match up", E_USER_WARNING);
            return null;
        }
    }

    /**
     * Returns an array with all the slideshows in this 'file'.
     * @return array {array} Associative array with 'slideshowId' => SlideShow.
     */
    public function getSlideShows()
    {
        if (!$this->checkAvail($this->slideShows)) {
            return null;
        }
        return $this->slideShows;
    }

    /**
     * Makes $settings useful, even for this version.
     * Called when the configuration file is either outdated or too new.
     * A dummy implementation.
     * @param $settings array See {@link parse_ini_file() parse_ini_file(filename, true)}
     * @return array $settings
     * @throws IncompatibleVersionException if $settings cannot possibly be useful in its current state.
     */
    protected function makeOldVersionCompatible($settings)
    {
        return $settings;
    }

    /**
     * Tries to load the given variable from file. This method, then, implements
     * the lazy loading.
     * @param $var mixed The variable which should be tried to load from file.
     * @return bool true if the loading was successful, throws exception if not.
     */
    protected function checkAvail(&$var)
    {
        if (!isset($var)) {
            if (isset($this->configFile) && $this->useFile) {
                $this->populateFromConfigFile();
                if (!isset($var)) {
                    throw new \RuntimeException('The requested variable did not load, even after reading from file. Misspelled?');
                } else {
                    return true;
                }
            } else {
                throw new \BadMethodCallException("Cannot read the requested field from SlideShowFile, since ".
                    "this object does not have any file associated to it");
            }
        } else {
            return true;
        }
    }

    /**
     * Add the given slide or list of slides.
     * @param SlideShow|array $slideShow SlideShows to add.
     */
    public function addSlideShow($slideShow)
    {
        if (
            !is_a($slideShow, 'SlideShow') &&
            (!is_array($slideShow) ||
                (count(array_filter(
                        $slideShow,
                        function ($value) { return is_a('SlideShow', $value); })) != count($slideShow)))
        ) {
            throw new \InvalidArgumentException('All slide shows must be an instance of SlideShow');
        }

        $this->checkAvail($this->slideShows);

        if (is_array($slideShow)) {
            $this->slideShows = array_merge($this->slideShows, $slideShow);
        } elseif (is_a($slideShow, 'SlideShow')) {
            $this->slideShows[$slideShow->getId()] = $slideShow;
        }
    }

    /**
     * Remove the given slideshow.
     * @param $slideShow string|SlideShow Either the id of the slideshow to remove, or the SlideShow itself.
     * @return bool true if changed, false if not
     */
    public function removeSlideShow($slideShow)
    {
        if (!$this->checkAvail($this->slideShows)) {
            return false;
        }

        if (is_string($slideShow) && isset($this->slideShows[$slideShow])) {
            unset ($this->slideShows[$slideShow]);
            return true;
        } else if (is_a($slideShow, 'SlideShow') && ($key = array_search($slideShow, $this->slideShows)) !== false) {
            unset ($this->slideShows[$key]);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Retrieve an external iterator that iterates through the slideshows in this object.
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An iterator that iterates over the slideshows in this object.
     */
    public function getIterator()
    {
        if (!$this->checkAvail($this->slideShows)) {
            return null;
        }
        return new \ArrayIterator($this->slideShows);
    }
}