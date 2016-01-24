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
 * @file Class representing a single view (slide) in a slideshow.
 * @author Thorben Werner Sjøstrøm Dahl <thorben@sjostrom.no>
 * @copyright Thorben Werner Sjøstrøm Dahl 2015
 * @license http://opensource.org/licenses/MIT The MIT License
 * @package tobinus\SimpleInfoscreen
 */

namespace tobinus\SimpleInfoscreen;

/**
 * Represents a single slide in a slideshow.
 *
 * A slide has a URL (which represents its content), a duration and a loadingTime.
 * It is possible to have multiple SlideShow instances reference the same Slide
 * object, since it contains no info on its users.
 * @package tobinus\SimpleInfoscreen
 */
class Slide
{
    protected $url, $duration, $loadingTime;

    /**
     * Create a new slide with the given $url, $duration and $loadingTime.
     * @uses setUrl()
     * @uses setDuration()
     * @uses setLoadingTime()
     */
    public function __construct($url, $duration, $loadingTime)
    {
        $this->setUrl($url);
        $this->setDuration($duration);
        $this->setLoadingTime($loadingTime);
    }

    /**
     * Get JavaScript-expression that recreates this instance of Slide in JavaScript.
     * Requires js/slideshow.js.
     *
     * Example of result:
     * <code>
     * new Slide('local/test.html', 13, 2)
     * </code>
     * This can be used to create a slideshow:
     *     <script>
     *     var slideShow = new SlideShow(<php $mySlide->generateJavaScript() >, ...);
     *     </script>
     * @return string {string} JavaScript expression that recreates this slide client-side.
     */
    public function generateJavaScript()
    {
        $url = preg_replace('/^local\//', 'load.php/', $this->url);
        $duration = $this->duration;
        $loadingTime = $this->loadingTime;

        return "new Slide('$url', $duration, $loadingTime)";
    }

    //////////////////////////////////
    ///      GETTERS / SETTERS     ///
    //////////////////////////////////

    /**
     * Get the URL associated with this slide.
     * @return string {string} URL of this slide.
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Assigns a new URL to this slide.
     * @param string {string} $newUrl The new URL. It can either be a relative path
     * from the application root, absolute or external (starting with e.g. http://).
     * @throws \InvalidArgumentException If new URL is empty. The URL is not checked
     * to see if it can actually be loaded.
     */
    public function setUrl($newUrl)
    {
        if (!empty($newUrl))
        {
            $this->url = $newUrl;
        } else
        {
            throw new \InvalidArgumentException('$newUrl cannot be empty');
        }
    }

    /**
     * @return integer The number of seconds for which this slide should be displayed.
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * @param integer $newDuration The number of seconds this slide should be displayed.
     */
    public function setDuration($newDuration)
    {
        if (($newDuration = intval($newDuration)) !== 0 && $newDuration > 0)
        {
            $this->duration = $newDuration;
        } else
        {
            throw new \InvalidArgumentException('New duration must be a positive integer, was '.$newDuration);
        }
    }

    /**
     * @return integer The number of seconds this slide should be given to load.
     */
    public function getLoadingTime()
    {
        return $this->loadingTime;
    }

    /**
     * @param integer $newLoadingTime The number of seconds this slide should be given to load.
     */
    public function setLoadingTime($newLoadingTime)
    {
        if (($newLoadingTime = intval($newLoadingTime)) >= 0)
        {
            $this->loadingTime = $newLoadingTime;
        } else
        {
            throw new \InvalidArgumentException('New loading time must be a non-negative integer, was '.$newLoadingTime);
        }
    }
}
