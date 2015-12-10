<?php
/**
 * Created by PhpStorm.
 * User: thorben
 * Date: 01.08.15
 * Time: 19:13
 */

namespace tobinus\SimpleInfoscreen;


class FileNotFound extends \RuntimeException
{

    /**
     * FileNotFound constructor.
     * @param string $filepath
     */
    public function __construct($filepath)
    {
    }
}