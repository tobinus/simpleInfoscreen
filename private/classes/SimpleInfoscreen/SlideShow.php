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
 * @file Representation of a slideshow, which contains several slides in order.
 * @author Thorben Werner Sjøstrøm Dahl <thorben@sjostrom.no>
 * @copyright Thorben Werner Sjøstrøm Dahl 2015
 * @license http://opensource.org/licenses/MIT The MIT License
 * @package tobinus\SimpleInfoscreen
 */

namespace tobinus\SimpleInfoscreen;


use Traversable;

/**
 * A representation of a collection of Slide. One slideshow plays at a time on
 * the infoscreen.
 * @package tobinus\SimpleInfoscreen
 */
class SlideShow implements \IteratorAggregate
{
    protected $slides;

    protected $id;

    protected $name;
    protected $i = 0;

    /**
     * Generates a JavaScript expression which represents this slideshow.
     *
     *
     * Example usage:
     * <code>
     * <script>
     * var mySlideShow = <?php $mySlideshow->generateJavaScript(); ?>;
     * </script>
     * </code>
     *
     * Example return value:
     * <code>
     * new SlideShow(
     * new Slide('local/test.html', 13, 2),
     * new Slide('local/foo.html', 10, 4))
     * </code>
     * @uses Slide::generateJavaScript
     * @return string JavaScript expression
     */
    public function generateJavaScript()
    {
        $code = "new SlideShow(\n";
        $code .= implode(",\n", array_map(
            function (Slide $slide) {return $slide->generateJavaScript();},
            $this->slides
        ));
        $code .= ')';
        return $code;
    }

    /**
     * Returns the number of slides in this slideshow.
     * @return int Number of slides in this slideshow.
     */
    public function size()
    {
        return count($this->slides);
    }

    /**
     * Returns the human-readable name for this slideshow.
     * @return string The human-readable name for this slideshow.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $name string New human-readable name for this slideshow.
     */
    public function setName($name)
    {
        if (preg_match('/^\s*$/', $name)) {
            throw new \InvalidArgumentException('The human-readable slide show name cannot be practically empty.');
        }

        $this->name = $name;
    }

    /**
     * Get the unique id for this slideshow. This id is used when referencing
     * a slideshow in a SlideShowFile.
     * @return string Unique SlideShow id.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets a new, unique id for this SlideShow.
     * NOTE: This must not be used on a SlideShow which is read from file, unless
     * you're ready to deal with inconsistency between this SlideShow and its
     * container, SlideShowFile.
     * @param $id string New, unique id.
     */
    public function setId($id)
    {
        if (preg_match('/^\s*$/', $id)) {
            throw new \InvalidArgumentException(
                'The slide show id must consist of at least one non-whitespace character.'
            );
        }

        $this->id = $id;
    }

    /**
     * Add the slide, or list of slides, to this SlideShow.
     * @param $slide Slide|array Either a new Slide to add, or an array containing
     * slides to add.
     * @param int|null $position
     */
    public function addSlide($slide, $position = null)
    {
        if (
            !($slide instanceof Slide) &&
            (!is_array($slide) ||
                (count(array_filter(
                        $slide,
                        function ($value) { return $value instanceof Slide; })) != count($slide)))
        ) {
            throw new \InvalidArgumentException('All slides must be an instance of Slide');
        }

        if ($position === null)
        {
            if (is_array($slide)) {
                $this->slides = array_merge($this->slides, $slide);
            } else {
                $this->slides[] = $slide;
            }
        } else
        {
            array_splice($this->slides, $position, 0, $slide);
        }
    }

    /**
     * Remove the given slide from this SlideShow.
     * @param $slide Slide|int Either the Slide to remove, or the position of the slide to remove.
     * @return bool true if changed, false otherwise.
     */
    public function removeSlide($slide)
    {

        if (is_int($slide) && $slide >= 0 && $slide < count($this->slides))
        {
            unset ($this->slides[$slide]);
            return true;
        } else if (is_a($slide, 'Slide') && ($key = array_search($slide, $this->slides)) !== false)
        {
            unset ($this->slides[$key]);
            return true;
        } else
        {
            return false;
        }
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->slides);
    }

    /**
     * Moves the specified slide so that it occupies the given index.
     * Slides between the current position and the new position will be shifted
     * to make space for the slide.
     * @param Slide $slide Slide to move.
     * @param $targetIndex int Index to move the slide to.
     */
    public function moveTo(Slide $slide, $targetIndex)
    {
        $fromIndex = array_search($slide, $this->slides);
        if ($fromIndex === false) {
            throw new \LogicException('$slide was not found in this slideshow');
        }
        $this->moveFromTo($fromIndex, $targetIndex);
    }

    /**
     * Moves the slide identified by its index to a new position.
     * Slides between the current position and the new position will be shifted
     * to make space for the slide.
     * @param $fromIndex int Index of the slide to be moved. Must be an existing index.
     * @param $targetIndex int The new index for the slide. Must be an existing index.
     */
    public function moveFromTo($fromIndex, $targetIndex)
    {
        // Validate
        $size = $this->size();
        if ($fromIndex < 0 || $fromIndex >= $size) {
            throw new \OutOfBoundsException(
                '$fromIndex was '.$fromIndex.
                ', boundary is from 0 to '.$size
            );
        } elseif ($targetIndex < 0 || $targetIndex >= $size) {
            throw new \OutOfBoundsException(
                '$targetIndex was '.$targetIndex.
                ', boundary is from 0 to '.$size
            );
        }

        // Remove the element from the array
        $out = array_splice($this->slides, $fromIndex, 1);
        // Add it back in the correct position
        array_splice($this->slides, $targetIndex, 0, $out);
    }

    /**
     * Move the given slide up or down by some amount.
     * Slides between the current position and the new position will be shifted
     * to make space for the slide at its new position.
     * @param Slide $slide The slide to be moved.
     * @param $difference int The amount of positions to move the slide.
     * Negative values will move the slide towards the start of the slideshow,
     * while positive values will move it towards the end. If the resulting index
     * is greater than the size of the slideshow, the slide will be moved to the very end.
     * If it results in being 0 or less, the slide will be moved to the very start.
     */
    public function moveSlideRelative(Slide $slide, $difference)
    {
        if (!is_int($difference)) {
            throw new \InvalidArgumentException('$difference must be integer, '. gettype($difference) . ' given');
        }
        $fromIndex = array_search($slide, $this->slides);
        if ($fromIndex === false) {
            throw new \LogicException('$slide was not found in this slideshow');
        }
        $toIndex = $fromIndex + $difference;
        // Ensure $toIndex stays within the bounds
        $toIndex = min($this->size()-1, max($toIndex, 0));
        $this->moveFromTo($fromIndex, $toIndex);
    }
}