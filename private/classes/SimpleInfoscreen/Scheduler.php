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
 * @file Class used for figuring out what SlideShow to display at any given time.
 * @author Thorben Werner Sjøstrøm Dahl <thorben@sjostrom.no>
 * @copyright Thorben Werner Sjøstrøm Dahl 2015
 * @license http://opensource.org/licenses/MIT The MIT License
 * @package tobinus\SimpleInfoscreen
 */

namespace tobinus\SimpleInfoscreen;


class Scheduler
{
    protected $time;
    protected $useScheduling;

    public function __construct($useScheduling = true, $time = null)
    {
        if ($time === null) {
            $time = time();
        }
        $this->time = $time;
        $this->setSchedulingStatus($useScheduling);
    }

    public function enableScheduling()
    {
        $this->useScheduling = true;
        return $this;
    }

    public function disableScheduling()
    {
        $this->useScheduling = false;
        return $this;
    }

    public function setSchedulingStatus($newStatus)
    {
        if (!is_bool($newStatus)) {
            throw new \InvalidArgumentException('New scheduling status must be true (enabled) or false (disabled)');
        }
        $this->useScheduling = boolval($newStatus);
        return $this;
    }

    public function getSchedulingStatus()
    {
        return $this->useScheduling;
    }

    public function getTime()
    {
        return $this->time;
    }

    public function setTime($newTime)
    {
        if (!is_numeric($newTime) || $newTime < 0) {
            throw new \InvalidArgumentException('Time must be a non-negative Unix timestamp (integer)');
        }
        $this->time = $newTime;
        return $this;
    }

    public function getSlideShowToUse(Settings $settings)
    {
        if (!$this->useScheduling) {
            return $settings->defaultSlideShow;
        } elseif (in_array(intval(date("w", $this->time)), [0, 6], true)) {
            return $settings->weekendSlideShow;
        } else {
            return $settings->weekdaySlideShow;
        }
    }

    public function getLastChange()
    {
        // Changes will not have happened if we're not scheduling
        if (!$this->useScheduling) {
            return false;
        }
        // A change will have happened last Saturday or last Monday
        $weekday = intval(date('w', $this->time));
        $mostRecentSaturday =
            ($weekday != 6)     // is it Saturday?
                ? strtotime('last Saturday midnight', $this->time) // no, find last Saturday
                : strtotime('today', $this->time); // yes, return midnight
        $mostRecentMonday =
            ($weekday != 1)    // is it Monday?
                ? strtotime('last Monday midnight', $this->time) // no, find last monday
                : strtotime('today', $this->time); // yes, return this day on midnight
        return max($mostRecentSaturday, $mostRecentMonday);
    }
}