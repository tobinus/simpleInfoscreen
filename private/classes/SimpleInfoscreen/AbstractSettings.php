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
 * A class that helps you read from a configuration file.
 * @author Thorben Werner Sjøstrøm Dahl <thorben@sjostrom.no>
 * @copyright Thorben Werner Sjøstrøm Dahl 2015
 * @license http://opensource.org/licenses/MIT The MIT License
 * @package tobinus\SimpleInfoscreen
 */

namespace tobinus\SimpleInfoscreen;

/**
 * Class AbstractSettings
 * Helper-class for validating and using configuration files (.ini).
 * This class might save you from some work, but it could be a bit difficult to
 * grasp the structure (not to mention it's overkill?). You should read up on
 * {@link parse_ini_file()} and its second option, which this class is built upon.
 * @package SimpleInfoscreen
 */
abstract class AbstractSettings
{
    /**
     * Create a new instance, using values from the config file or cache, if applicable.
     * The easiest way is to read the config file with {@link readFromConfigFile()},
     * and assign those options to fields while making sure default values are used
     * when they are not found in the configuration file.
     * @uses readFromConfigFile()
     */
   // public abstract function __construct();

    /**
     * MAIN - Populates this instance with setting values from config file.
     * Called as soon as the options are needed (lazy loading).
     * @return void
     */
    protected abstract function populateFromConfigFile();


    /**
     * Returns the filename of the .ini-file to be used.
     * @return {string} path/to/file.ini, relative to the application root
     */
    protected abstract function getConfigFilename();

    /**
     * Returns the expectations for the different entries in the configuration file.
     * Say you have this configuration file:
     *
     *
     *      version = 0.1
     *      [Section1]
     *          option1 = yes
     *          option2 = 2.4
     *          hobbies[] = fishing
     *          hobbies[] = sports
     *          hobbies[] = "watching movies"
     *      [Section2]
     *          option3 = someOtherValue
     *          option4 = 3
     *          option5 = apple
     *
     *
     * The expectations could then be like this:
     *
     *
     *      $expectations =
     *      [
     *          'Section1' =>
     *          [
     *              'option1' => 'bool',
     *              'option2' => 'float',
     *              'hobbies' => 'array'
     *          ],
     *          'Section2' =>
     *          [
     *              'option3' => 'string',
     *              'option4' => 'int',
     *              'option5' => ['orange', 'banana', 'apple', 'strawberry']
     *          ]
     *      ]
     *
     *
     * * option1 must be 'bool'/'boolean'; either yes/true/on/1 or no/false/off/(empty)
     * * option2 must be 'float'; aka numeric.
     * * hobbies must be 'list'/'array'.
     * * option3 must be 'string'; aka not empty.
     * * option4 must be 'int'/'integer'; aka numeric.
     * * option5 must be one of either orange, banana, apple or strawberry (case-sensitive).
     * @return array Multidimensional, assossiative array with information about expected values. See long description.
     */
    protected abstract function getExpectations();

    /**
     * Returns the newest version number for this program.
     * Used when deciding compatibility.
     * @return {string} Newest version number.
     */
    protected abstract function getNewestVersionNumber();



    /**
     * Returns array with names of fields that can be accessed through {@link __get()}.
     * Not really needed if {@link __get()} is overridden.
     * @return array Array with strings that name the fields that others can access,
     * but not set.
     */
    protected abstract function getAccessibleFields();

    /**
     * Fetches the value of the given option.
     * @param $setting string {string} Name of the setting you want to access.
     * @return mixed Value of the given option, or null if not found.
     * @uses getAccessibleFields()
     * @uses populateFromConfigFile()
     */
    public function __get($setting)
    {
        if (in_array($setting, $this->getAccessibleFields()))
        {
            if (!isset($this->$setting))
            {
                $this->populateFromConfigFile();
            }
            return $this->$setting;
        }

        $trace = debug_backtrace();
        trigger_error(
            'Undefined property via __get(): ' . $setting .
            ' in ' . $trace[0]['file'] .
            ' on line ' . $trace[0]['line'],
            E_USER_NOTICE);
        return null;
    }



    /**
     * Returns an array with valid options.
     * @return array In the same form as {@link parse_ini_file() parse_ini_file($filename, true)}
     * @source
     */
    protected function readFromConfigFile($file = null, $expectations = null)
    {
        // Get values (from extending classes)
        if ($file === null) $file = $this->getConfigFilename();
        if ($expectations === null) $expectations = $this->getExpectations();
        $version = $this->getNewestVersionNumber();

        // Read and parse ini-file
        $settings = $this->convertIniToArray($file);
        // Make custom changes or halt execution if version numbers mismatch
        $settings = $this->validateFileVersion($version, $file, $settings);

        // Validate each entry of $settings, using $expectations
        return $this->validateOptions($settings, $expectations, $file);
    }

    /**
     * Reads the contents of an ini-file, using {@link parse_ini_file()}.
     * @param $filename {string} The path to the file which shall be read from, relative to the application root.
     * @return array See {@link parse_ini_file()}, with second parameter set to true.
     * @see parse_ini_file()
     * @throws \RuntimeException If the supplied file caused parse errors.
     */
    protected function convertIniToArray($filename)
    {
        $settings = parse_ini_file($filename, true);

        // Was it parsed successfully?
        if (!$settings)
        {
            throw new \RuntimeException("There was an error in the configuration file $filename.");
        }
        return $settings;
    }

    /**
     * Makes changes to $settings, so that they fit this version of the application.
     * @param $version {string} Version number, as supplied by the ini-file.
     * @param $file {string} Configuration file in question. Used only for error messages.
     * @param $settings {array} See {@link parse_ini_file() parse_ini_file($filename, true)}.
     * @see parse_ini_file()
     * @see fileIsCompatible()
     * @throws IncompatibleVersionException if the configuration file and the program cannot possibly get together.
     * @return array $settings, but fixed to match this version (if necessary)
     */
    protected function validateFileVersion($version, $file, $settings)
    {
        // Is the configuration file compatible with this version of the program?
        if (!array_key_exists('version', $settings) ||
            !$this->fileIsCompatible($settings['version'])
        )
        {
            trigger_error("Config file <b>$file</b> is not fully compatible with this version ($version)." .
                "Defaults are used were applicable", E_USER_WARNING);
            $settings = $this->makeOldVersionCompatible($settings);
            return $settings;
        }
        return $settings;
    }

    /**
     * Checks if the specified version is compatible with this program.
     * @param $version {string} The version to check if is compatible.
     * @return bool True if the config file can be used with this program and class, false otherwise
     */
    protected function fileIsCompatible($version)
    {
        return $version == $this->getNewestVersionNumber();
    }

    /**
     * Makes $settings useful, even for this version.
     * Called when the configuration file is either outdated or too new.
     * A dummy implementation could just return $settings.
     * @param $settings array See {@link parse_ini_file() parse_ini_file(filename, true)}
     * @return array $settings
     * @throws IncompatibleVersionException if $settings cannot possibly be useful in its current state.
     */
    protected abstract function makeOldVersionCompatible($settings);

    /**
     * Validates every entry in $settings against the corresponding entry in $expectations.
     * @param $settings {array} Two-dimensional, associative array with 'version',
     * other options outside sections and sections in first dimension, and options in the second.
     * In other words: the return value from {@link parse_ini_file()} @see parse_ini_file()
     * @param $expectations {array} Same structure as $settings, but the values
     * indicate expected type or value. {@link getExpectations() More details} @see getExpectations()
     * @param $file {string} Name of the configuration file in question. Used in error messages.
     * @param bool $checkUnused Set to false to suppress error messages about
     * unexpected headings and settings.
     * @return array Multidimensional, associative array with sections and settings, like
     * {@link parse_ini_file() parse_ini_file($filename, true)}. $settings, just validated.
     * Invalid options are not set, and should be set to a default.
     */
    protected function validateOptions($settings, $expectations, $file, $checkUnused = true)
    {
        $newSettings = array();

        // Validate all the content of the file
        foreach ($settings as $section => $content)
        {
            // This was validated prevously
            if ($section == 'version')
            {
                continue;
            }

            // Are we expecting this section? (order irrelevant)
            if (!array_key_exists($section, $expectations))
            {
                // Nope
                if ($checkUnused) {
                    trigger_error("Unexpected heading <b>$section</b> in the configuration" .
                        " file <b>$file</b>");
                }
            } else
            {
                $sectionExpectations = $expectations[$section];

                // Wait, is it actually a section?
                if (is_array($sectionExpectations) && !isset($sectionExpectations[0]))
                {
                    $newSettings[$section] = array();
                    // Go through all the entries in this section

                    foreach ($content as $entryName => $entryValue)
                    {
                        // Are we expecting this entry?
                        if (!array_key_exists($entryName, $sectionExpectations))
                        {
                            if ($checkUnused) {
                                trigger_error("Unexpected entry <b>[$section] $entryName</b> " .
                                    " in <b>$file</b>");
                            }
                        } else
                        {
                            try
                            {
                                $newSettings[$section][$entryName] = $this->validateOption($sectionExpectations[$entryName], $entryValue, $entryName);
                            } catch (\InvalidArgumentException $e)
                            {
                                // Value cannot be used.
                                trigger_error("Configuration error in file <b>$file</b>, " .
                                    "section <b>$section</b>, entry <b>$entryName</b>: " .
                                    $e->getMessage() . ". Default value is used instead", E_USER_WARNING);
                            }
                        }
                    }
                } else
                {
                    // Not a section
                    try
                    {
                        $newSettings[$section] = $this->validateOption($sectionExpectations, $content, $section);
                    } catch (\InvalidArgumentException $e)
                    {
                        // Value cannot be used.
                        trigger_error("Configuration error in file <b>$file</b>, " .
                            " entry <b>$section</b>: " . $e->getMessage() .
                            ". Default value is used instead", E_USER_WARNING);
                    }

                }
            }
        }
        return $newSettings;
    }

    /**
     * Validates the entry, and returns it as the proper type.
     * @param $expected {array|string} Either array of valid values, or the name of a type.
     * Valid type names:
     *
     * * bool/boolean,
     * * int/integer,
     * * float,
     * * string/str,
     * * array/list,
     * * <whatever> (if you've implemented $this->validate<Whatever>($value, $remainingExpectationString))
     *
     * @param $value {string} The value to be tested and converted.
     * @return mixed $value, converted to the expected type.
     * @throws \InvalidArgumentException If the value doesn't meet the expectation.
     */
    protected function validateOption($expected, $value, $key)
    {
        // Should this be a specific type, or should it be an option
        if (is_array($expected))
        {
            // Options!
            if (array_search($value, $expected) !== false)
            {
                // It was one of the options
                return $value;
            } else
            {
                throw new \InvalidArgumentException
                (
                    "expected value to be one of " . json_encode($expected) .
                    ", but it was \"$value\""
                );
            }
        } else
        {
            // If there's a space, then make sure we're using first word only
            if (($spacepos = mb_strpos($expected, " ")) !== false)
            {
                $expectedType = substr($expected, 0, $spacepos);
                $expectedOptions = substr($expected, $spacepos + 1);
            } else
            {
                $expectedType = $expected;
                $expectedOptions = "";
            }
            // Check its type
            switch($expectedType) {
                case 'boolean':
                case 'bool':
                    if ($value == "" || $value == "1")
                    {
                        return boolval($value);
                    } else
                    {
                        throw new \InvalidArgumentException
                        (
                            "expected boolean, value was \"$value\""
                        );
                    }
                case 'int':
                case 'integer':
                case 'float':
                    if (is_numeric($value))
                    {
                        if ($expectedType == 'float')
                        {
                            return floatval($value);
                        } else
                        {
                            return intval($value);
                        }
                    } else
                    {
                        throw new \InvalidArgumentException
                        (
                            "expected $expectedType, value was \"$value\""
                        );
                    }
                case 'string':
                case 'str':
                    if (!empty($value))
                    {
                        return $value;
                    } else
                    {
                        throw new \InvalidArgumentException
                        (
                            "expected a string, value was empty"
                        );
                    }
                case 'array':
                case 'list':
                    if (is_array($value))
                    {
                        $newValue = array();
                        foreach($value as $entryName => $entry)
                        {
                            $newValue[$entryName] = $this->validateOption($expectedOptions, $entry, $entryName);
                        }
                        return $newValue;
                    }else
                    {
                        throw new \InvalidArgumentException
                        (
                            "expected an array, value was \"$value\""
                        );
                    }

                case 'date':
                case 'datetime':
                    if (!strtotime($value)) {
                         throw new \InvalidArgumentException
                         ("date was be in a format accepted by strtotime(), but it was \"$value\"");
                    }
                    return new \DateTime($value);

                case 'arrayKeys':
                case 'listKeys':
                    if (is_array($value) && $expectedOptions != "") {
                        $expectedKeys = array_flip(explode(" ", $expectedOptions));
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
                        $errorStart = "Config file $file, section infoscreen:";

                        if ($numMissingKeys > 0 && $numUnrecognizedKeys > 0) {
                            trigger_error("$errorStart Option entries {$key}[".implode("], {$key}[", array_keys($missingKeys)).
                                "] were expected, but not found. Additionally, the following entries were".
                                " found but not recognized: {$key}[".implode("], {$key}[", array_keys($unrecognizedKeys)).
                                "]. Perhaps some of them were misspelled?");
                        } elseif($numUnrecognizedKeys > 0) {
                            trigger_error("$errorStart The following entries were found, but not recognized: ".
                                "{$key}[" . implode("], {$key}[", array_keys($unrecognizedKeys)) . "].".
                                " This likely indicates that the config file is aiming at a newer version".
                                " of Infoscreen, which might regonize those entries. Perhaps upgrade?");
                        }

                        return array_intersect_key($value, $expectedKeys);
                    } elseif (!is_array($value)) {
                        throw new \InvalidArgumentException('expected array (use [])');
                    } elseif ($expectedOptions == "") {
                        throw new \BadMethodCallException(
                            '$expectations line for '.$key.' does not name any keys to look for');
                    }
                break;

                default:
                    if (method_exists($this, $methodName = "validate" . ucfirst($expectedType)))
                    {
                        return call_user_func(array($this, $methodName), $value, $expectedOptions, $key);
                    } else
                    {
                        throw new \BadMethodCallException
                        (
                            "The value of \$expected, \"" . $expectedType . "\", was not recognized. " .
                            "See PHPDoc for usage information."
                        );
                    }
                    break;
            }
        }
    }
}