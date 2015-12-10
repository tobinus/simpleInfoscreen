<?php
/**
 * Created by PhpStorm.
 * User: thorben
 * Date: 30.07.15
 * Time: 21:41
 */

namespace tobinus\SimpleInfoscreen;

// Set up the Twig autoloader
require_once TWIGDIR . '/lib/Twig/Autoloader.php';
\Twig_Autoloader::register();

class Template
{
    public static function init()
    {
        global $PRODUCTION;
        $loader = new \Twig_Loader_Filesystem(TEMPLATEDIR);
        $twig = new \Twig_Environment($loader, array(
            'cache' => DATADIR . '/template_cache',
            'debug' => !$PRODUCTION,
            'strict_variables' => !$PRODUCTION,
        ));
        $dayExt = new \Twig_SimpleFilter('day', [__CLASS__, 'day']);
        $twig->addFilter($dayExt);
        return $twig;
    }

    /**
     * Transform a date into a translated string of the name of the day.
     *
     * Supported languages:
     * * English (eng)
     * * Norwegian Bokmål / Norsk bokmål (nob)
     * * Norwegian Nynorsk / Norsk nynorsk (nno)
     * @param mixed $date DateTime, string compatible with strtotime() or Unix timestamp
     * @param string $language ISO 639-2 or ISO 639-1 language code. See long
     * description for list of supported languages.
     * @param string $form Whether to use full name of day, or short – (f = full, s = short)
     * @return string Name of the day of the given date, translated to the given
     * language in the given form.
     * @throws \ErrorException If language is not supported.
     */
    public static function day($date, $language, $form) {
        // If DateTime: use as is
        // If string: use as strtotime input
        // If none of those: assume it is int, handle as Unix timestamp
        $datetime = $date instanceof \DateTime ? $date : (is_string($date) ? new \DateTime($date) : new \DateTime('@' . $date));

        $day = $datetime->format('N');
        static $longForms = ['full', 'f', 'long', 'l'];
        static $shortForms = ['short', 's'];
        if (in_array($form, $longForms)) {
            $useFull = true;
        } elseif (in_array($form, $shortForms)) {
            $useFull = false;
        } else {
            throw new \InvalidArgumentException('form argument to day filter was not recognized');
        }

        // If english: use built-in
        $englishCodes = ['en', 'eng'];
        if (in_array($language, $englishCodes)) {
            return $useFull ? $datetime->format('l') : $datetime->format('D');
        }

        switch ($language) {
            case 'no':
            case 'nb':
            case 'nob':
                // Norsk Bokmål
                if ($useFull) {
                    $days = [
                        1 => 'mandag',
                        2 => 'tirsdag',
                        3 => 'onsdag',
                        4 => 'torsdag',
                        5 => 'fredag',
                        6 => 'lørdag',
                        7 => 'søndag',
                    ];
                } else {
                    $days = [
                        1 => 'man',
                        2 => 'tirs',
                        3 => 'ons',
                        4 => 'tors',
                        5 => 'fre',
                        6 => 'lør',
                        7 => 'søn',
                    ];
                }
                break;
            case 'nno':
            case 'nn':
                // Norsk Nynorsk
                if ($useFull) {
                    $days = [
                        1 => 'måndag',
                        2 => 'tysdag',
                        3 => 'onsdag',
                        4 => 'torsdag',
                        5 => 'fredag',
                        6 => 'laurdag',
                        7 => 'sundag',
                    ];
                } else {
                    $days = [
                        1 => 'mån',
                        2 => 'tys',
                        3 => 'ons',
                        4 => 'tors',
                        5 => 'fre',
                        6 => 'laur',
                        7 => 'sun',
                    ];
                }
                break;
            default:
                throw new \LogicException('Language ' . $language . ' is not supported by the fullDay function');
        }
        return $days[$day];
    }
}