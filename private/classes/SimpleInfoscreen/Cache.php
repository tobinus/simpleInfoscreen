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
 * A class that lets you manipulate a cache file.
 * @author Thorben Werner Sjøstrøm Dahl <thorben@sjostrom.no>
 * @copyright Thorben Werner Sjøstrøm Dahl 2015
 * @license http://opensource.org/licenses/MIT The MIT License
 * @package tobinus\SimpleInfoscreen
 */

namespace tobinus\SimpleInfoscreen;

use \DateTime;
use \InvalidArgumentException;

/**
 * Class representing a cache file, which you can use to easily find out whether a
 * cache exists or is fresh, and use that cache or create a new one.
 * @package tobinus\SimpleInfoscreen
 */
class Cache
{
    private $filename;
    private $filepath;
    private $expirationTime;
    private $etag;
    private $modificationTime;
    private $hasSentCacheHeaders = false;
    private $isReadable;
    private $isFresh;

    /**
     * Create new Cache object from the given filename and expirationtime.
     * @param string $filename Name of file to use as cache (inside html_cache
     * folder, without any slashes).
     * @param mixed $expirationTime Unix timestamp or DateTime-object representing
     * the time which the cache should be newer than. Used only when reading.
     * Default is 1 one hour ago.
     */
    public function __construct($filename, $expirationTime = 0)
    {
        $this->setFilename($filename);
        $this->setExpirationTime($expirationTime);
    }

    /**
     * @return string Return the filename of this cache (without path).
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @param string $filename Name of file to use as cache (inside outside_web/data/html_cache),
     * without cache.
     */
    protected function setFilename($filename)
    {
        if (empty($filename)) {
            throw new InvalidArgumentException('filename cannot be empty');
        }
        $this->filename = basename($filename);
        $this->filepath = DATADIR . '/html_cache/' . $this->filename;
    }


    public function moveTo($filename)
    {
        if ($this->exists()) {
            $oldFilepath = $this->getFilepath();
            $oldFilename = $this->getFilename();
            try {
                $this->setFilename($filename);
                if (!rename($oldFilepath, $this->getFilepath())) {
                    $renameError = error_get_last();
                    throw new \ErrorException($renameError['message'], 0, $renameError['type'], $renameError['file'], $renameError['line']);
                }

            } catch (\Exception $e) {
                $this->setFilename($oldFilename);
                throw $e;
            }
        }
    }

    /**
     * @return string Get the full (local) path to the cache file.
     */
    public function getFilepath()
    {
        return $this->filepath;
    }

    /**
     * @return int Get the Unix timestamp which marks the threshold between a
     * fresh cache and a stale one.
     */
    public function getExpirationTime()
    {
        return $this->expirationTime;
    }

    /**
     * @param mixed $expirationTime DateTime object or Unix timestamp which represents
     * the threshold between a fresh and a stale cache
     */
    public function setExpirationTime($expirationTime = 0)
    {
        if ($expirationTime === 0) {
            $expirationTime = new DateTime('-1 hour');
        } elseif (is_a($expirationTime, 'DateTime')) {
            $expirationTime = $expirationTime->getTimestamp();
        } elseif (!is_int($expirationTime) && !is_numeric($expirationTime)) {
            throw new InvalidArgumentException('expiration time must either be DateTime object or Unix timestamp.');
        }

        $this->expirationTime = $expirationTime;
    }

    /**
     * @return bool True if the cache can be read from.
     */
    public function exists($reload = false)
    {
        if (!isset($this->isReadable) || $reload) {
            $this->isReadable = is_readable($this->filepath);
        }
        return $this->isReadable;
    }

    /**
     * @return bool True if the cache can be read from, and is fresh.
     */
    public function isFresh($reload = false)
    {
        try {
            if (!isset($this->isFresh) || $reload) {
                $this->isFresh = ($this->getModificationTime() >= $this->expirationTime);
            }
            return $this->isFresh;
        } catch (\RuntimeException $e) {
            return false;
        }
    }

    /**
     * Assert that the cache is readable, throw exception otherwise.
     * @param bool|false $ignoreExpiration Set to true to avoid checking if
     * cache is fresh.
     * @throws CacheDoesNotExist
     * @throws StaleCache
     */
    protected function validate($ignoreExpiration = false)
    {
        if (!$this->exists()) {
            throw new CacheDoesNotExist("The cachefile $this->filename (evaluated to $this->filepath) doesn't exist, or it is not readable.");
        }

        // There is a cache, is it valid?
        if (!$ignoreExpiration && !$this->isFresh()) {
            throw new StaleCache("The cachefile $this->filename (evaluted to $this->filepath) is not fresh");
        }
    }

    /**
     * @return bool True if this cache can (and should) be used. That is, if
     * the cache file is readable, and is fresh.
     */
    public function isValid()
    {
        return ($this->exists() && $this->isFresh()); // stay fresh
    }

    /**
     * @param bool|false $ignoreTime Set to true to get the cache contents, even
     * if it is stale.
     * @return string The cache content.
     * @see output() if you want to output the cache contents instead of return it.
     */
    public function get($ignoreTime = false)
    {
        $this->validate($ignoreTime);
        return file_get_contents($this->filepath);
    }

    /**
     * Outputs the cache content, or tell the browser to use its cache (if it's fresh).
     * @param bool|false $ignoreTime Set to true to output the cache contents,
     * even if it is stale.
     * @see get() if you want the cache contents returned instead of outputted.
     */
    public function output($ignoreTime = false, $useBrowserCache = true)
    {
        $this->validate($ignoreTime);
        if ($useBrowserCache && $this->browserHasFreshCache()) {
            $this->useBrowserCache();
        } else {
            $this->enableBrowserCaching();
            readfile($this->filepath);
        }
    }

    /**
     * @return bool True if the cache file can be created.
     */
    public function isWritable()
    {
        return (
            ($this->exists() && is_writable($this->filepath))
            || (!$this->exists() && is_writable(dirname($this->filepath)))
        );
    }

    /**
     * Create or update the cache with the given content.
     * @param string $input The content to populate the cache with.
     * @return int Number of bytes written to the cache file, or false if it failed.
     */
    public function write($input, $ignoreErrors = false)
    {
        global $ERROR_HAS_OCCURRED;
        if (empty($input)) {
            throw new InvalidArgumentException('Input cannot be empty');
        }
        if (!$ignoreErrors && $ERROR_HAS_OCCURRED) {
            throw new \RuntimeException('An error has already occurred, let\'s not cache that error');
        }
        unset($this->isReadable);
        unset($this->isFresh);
        unset($this->modificationTime);
        unset($this->etag);
        if (!file_exists(dirname($this->filepath))) {
            // Try to create directory if it doesn't exist yet
            mkdir($this->filepath, 0770);
        }
        // Make an atomic update (assuming Unix system)
        $tmpFilePath = $this->filepath . ".tmp";
        $result = file_put_contents($tmpFilePath, $input, LOCK_EX);
        rename($tmpFilePath, $this->filepath);
        return $result;
    }

    /**
     * Invalidate the cache by removing the cache file, thus requiring the content
     * to be regenerated.
     */
    public function invalidate()
    {
        if ($this->exists()) {
            unlink($this->filepath);
            unset($this->isFresh);
            unset($this->isReadable);
            unset($this->modificationTime);
            unset($this->etag);
        }
    }

    /**
     * Alias for {@link invalidate}.
     */
    public function remove()
    {
        $this->invalidate();
    }

    public function getEtag($reload = false)
    {
        if (!empty($this->etag) && !$reload) {
            return $this->etag;
        } else if ($this->exists()) {
            $this->etag = md5_file($this->filepath);
            return $this->etag;
        } else {
            throw new CacheDoesNotExist('Cannot get etag when the cache file does not exist.');
        }
    }

    public function getModificationTime($reload = false)
    {
        if (!empty($this->modificationTime) && !$reload) {
            return $this->modificationTime;
        } else if ($this->exists()) {
            $this->modificationTime = filemtime($this->filepath);
            return $this->modificationTime;
        } else {
            throw new CacheDoesNotExist('Cannot get modification time when the cache file does not exist');
        }
    }

    public function externalHasFreshCache($extLastModified = null, $extEtag = null)
    {
        return (
               $extLastModified === $this->getModificationTime()
            || $extEtag === $this->getEtag()
        );
    }

    public function browserHasFreshCache()
    {
        return $this->externalHasFreshCache(
            @strtotime(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : false),
            isset($_SERVER['HTTP_IF_NONE_MATCH']) ? trim($_SERVER['HTTP_IF_NONE_MATCH']) : false
        );
    }

    public function enableBrowserCaching($force = false)
    {
        if (headers_sent() || ($this->hasSentCacheHeaders && !$force)) {
            return;
        }
        // set last modified header
        header("Last-Modified: " . gmdate("D, d M Y H:i:s", $this->getModificationTime()) . " GMT");
        // set etag-header
        header("Etag: {$this->getEtag()}");
        // make sure caching is turned on, but revalidated every time
        header('Cache-Control: public,max-age=0,must-revalidate');
        $this->hasSentCacheHeaders = true;
    }

    public function useBrowserCache($force = false)
    {
        if (!headers_sent() && ($this->browserHasFreshCache() || $force)) {
            if (!$force) $this->enableBrowserCaching();
            header("HTTP/1.1 304 Not Modified");
        } else {
            throw new \RuntimeException('The browser cache is stale and cannot be used (unless forced)');
        }
    }

    /**
     * Writes $input to cachefile, and prints it out to the browser.
     * @param $input string The content which should be saved and output
     * @see write()
     */
    public function writeAndOutput($input) {
        try {
            $this->write($input);
            $this->enableBrowserCaching();
        } finally {
            // output the page, even if the cache fails to be written to
            echo $input;
        }
    }
}