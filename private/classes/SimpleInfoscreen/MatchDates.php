<?php
/**
 * Created by PhpStorm.
 * User: thorben
 * Date: 05.08.15
 * Time: 21:56
 */

namespace tobinus\SimpleInfoscreen;


class MatchDates extends AbstractSettings
{
    public function __construct() {
        $this->populateFromConfigFile();
    }

    protected $dates;

    /**
     * @inheritDoc
     */
    protected function populateFromConfigFile()
    {
        $settings = $this->readFromConfigFile();
        $dates = isset($settings['dates']) ? $settings['dates'] : [];
        set_unless_defined($dates['default'], new \DateTime('sunday'));
        $this->dates = $dates;
    }

    /**
     * @inheritDoc
     */
    protected function getConfigFilename()
    {
        return CONFIGDIR . '/matchdates.ini';
    }

    /**
     * @inheritDoc
     */
    protected function getExpectations()
    {
        return [
            'dates' => 'array matchdate',
            'version' => 'float',
        ];
    }

    /**
     * @inheritDoc
     */
    protected function getNewestVersionNumber()
    {
        return 0.1;
    }

    /**
     * @inheritDoc
     */
    protected function getAccessibleFields()
    {
    }

    /**
     * @inheritDoc
     */
    protected function makeOldVersionCompatible($settings)
    {
        return $settings;
    }

    public function getDate($name) {
        if (isset($this->dates[$name])) {
            return $this->dates[$name];
        } else {
            throw new \RuntimeException("The date $name was not found in the configuration file.");
        }
    }

    /**
     * Make sure a valid date is used, but return the strtotime string, not the resulting date
     * @param $value
     * @param $remainder
     */
    protected function validateMatchdate($value, $remainder) {
        if (strtotime($value) === false) {
            throw new \InvalidArgumentException("Date value \"$value\" couldn't be parsed.");
        }
        return $value;
    }
}