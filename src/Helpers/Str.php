<?php

namespace Fundevogel\Pcbis\Helpers;

use Fundevogel\Pcbis\Helpers\A;

use Exception;

if(!defined('MB')) define('MB', (int)function_exists('mb_get_info'));


/**
 * The String class provides a set
 * of handy methods for string
 * handling and manipulation.
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Str
{
    /**
     * Language translation table
     *
     * @var array
     */
    public static $language = [];


    /**
     * Ascii translation table
     *
     * @var array
     */
    public static $ascii = [
        '/°|₀/' => '0',
        '/¹|₁/' => '1',
        '/²|₂/' => '2',
        '/³|₃/' => '3',
        '/⁴|₄/' => '4',
        '/⁵|₅/' => '5',
        '/⁶|₆/' => '6',
        '/⁷|₇/' => '7',
        '/⁸|₈/' => '8',
        '/⁹|₉/' => '9',
        '/À|Á|Â|Ã|Å|Ǻ|Ā|Ă|Ą|Ǎ|Ä|A/' => 'A',
        '/à|á|â|ã|å|ǻ|ā|ă|ą|ǎ|ª|æ|ǽ|ä|a|а/' => 'a',
        '/Б/' => 'B',
        '/б/' => 'b',
        '/Ç|Ć|Ĉ|Ċ|Č|Ц/' => 'C',
        '/ç|ć|ĉ|ċ|č|ц/' => 'c',
        '/Ð|Ď|Đ/' => 'Dj',
        '/ð|ď|đ/' => 'dj',
        '/Д/' => 'D',
        '/д/' => 'd',
        '/È|É|Ê|Ë|Ē|Ĕ|Ė|Ę|Ě|Е|Ё|Э/' => 'E',
        '/è|é|ê|ë|ē|ĕ|ė|ę|ě|е|ё|э/' => 'e',
        '/Ф/' => 'F',
        '/ƒ|ф/' => 'f',
        '/Ĝ|Ğ|Ġ|Ģ|Г/' => 'G',
        '/ĝ|ğ|ġ|ģ|г/' => 'g',
        '/Ĥ|Ħ|Х/' => 'H',
        '/ĥ|ħ|х/' => 'h',
        '/Ì|Í|Î|Ï|Ĩ|Ī|Ĭ|Ǐ|Į|İ|И/' => 'I',
        '/ì|í|î|ï|ĩ|ī|ĭ|ǐ|į|ı|и|i̇/' => 'i',
        '/Ĵ|Й/' => 'J',
        '/ĵ|й/' => 'j',
        '/Ķ|К/' => 'K',
        '/ķ|к/' => 'k',
        '/Ĺ|Ļ|Ľ|Ŀ|Ł|Л/' => 'L',
        '/ĺ|ļ|ľ|ŀ|ł|л/' => 'l',
        '/М/' => 'M',
        '/м/' => 'm',
        '/Ñ|Ń|Ņ|Ň|Н/' => 'N',
        '/ñ|ń|ņ|ň|ŉ|н/' => 'n',
        '/Ò|Ó|Ô|Õ|Ō|Ŏ|Ǒ|Ő|Ơ|Ø|Ǿ|Ö|O/' => 'O',
        '/ò|ó|ô|õ|ō|ŏ|ǒ|ő|ơ|ø|ǿ|º|ö|o|о/' => 'o',
        '/П/' => 'P',
        '/п/' => 'p',
        '/Ŕ|Ŗ|Ř|Р/' => 'R',
        '/ŕ|ŗ|ř|р/' => 'r',
        '/Ś|Ŝ|Ş|Ș|Š|С/' => 'S',
        '/ś|ŝ|ş|ș|š|ſ|с/' => 's',
        '/Ţ|Ț|Ť|Ŧ|Т/' => 'T',
        '/ţ|ț|ť|ŧ|т/' => 't',
        '/Ù|Ú|Û|Ũ|Ū|Ŭ|Ů|Ű|Ų|Ư|Ǔ|Ǖ|Ǘ|Ǚ|Ǜ|У|Ü|U/' => 'U',
        '/ù|ú|û|ũ|ū|ŭ|ů|ű|ų|ư|ǔ|ǖ|ǘ|ǚ|ǜ|у|ü|u/' => 'u',
        '/В/' => 'V',
        '/в/' => 'v',
        '/Ý|Ÿ|Ŷ|Ы/' => 'Y',
        '/ý|ÿ|ŷ|ы/' => 'y',
        '/Ŵ/' => 'W',
        '/ŵ/' => 'w',
        '/Ź|Ż|Ž|З/' => 'Z',
        '/ź|ż|ž|з/' => 'z',
        '/Æ|Ǽ/' => 'AE',
        '/ß/' => 'ss',
        '/Ĳ/' => 'IJ',
        '/ĳ/' => 'ij',
        '/Œ/' => 'OE',
        '/Ч/' => 'Ch',
        '/ч/' => 'ch',
        '/Ю/' => 'Ju',
        '/ю/' => 'ju',
        '/Я/' => 'Ja',
        '/я/' => 'ja',
        '/Ш/' => 'Sh',
        '/ш/' => 'sh',
        '/Щ/' => 'Shch',
        '/щ/' => 'shch',
        '/Ж/' => 'Zh',
        '/ж/' => 'zh',
    ];


    /**
     * Tries to detect the string encoding
     *
     * @param string $string
     * @return string
     */
    public static function encoding(string $string): string
    {
        return mb_detect_encoding($string, 'UTF-8, ISO-8859-1, windows-1251', true);
    }


    /**
     * An UTF-8 safe version of substr()
     *
     * @param  string  $str
     * @param  int     $start
     * @param  int     $length
     * @return string
     */
    public static function substr($str, $start, $length = null)
    {
        $length = $length === null ? static::length($str) : $length;
        return MB ? mb_substr($str, $start, $length, 'UTF-8') : substr($str, $start, $length);
    }


    /**
     * An UTF-8 safe version of strtolower()
     *
     * @param  string  $str
     * @return string
     */
    public static function lower($str)
    {
        return MB ? mb_strtolower($str, 'UTF-8') : strtolower($str);
    }


    /**
     * An UTF-8 safe version of strlen()
     *
     * @param string  $str
     *
     * @return int
     *
     * @psalm-return 0|positive-int
     */
    public static function length($str)
    {
        return MB ? mb_strlen($str, 'UTF-8') : strlen($str);
    }


    /**
     * Checks if a str contains another string
     *
     * @param string  $str
     * @param string  $needle
     * @param boolean $i ignore upper/lowercase
     */
    public static function contains($str, $needle, $i = true): bool
    {
        if($i) {
            $str    = static::lower($str);
            $needle = static::lower($needle);
        }

        return strstr($str, $needle) ? true : false;
    }


    /**
     * Replaces all or some occurrences of the search string with the replacement string
     * Extension of the str_replace() function in PHP with an additional $limit parameter
     *
     * @param  string|array $string  String being replaced on (haystack);
     *                               can be an array of multiple subject strings
     * @param  string|array $search  Value being searched for (needle)
     * @param  string|array $replace Value to replace matches with
     * @param  int|array    $limit   Maximum possible replacements for each search value;
     *                               multiple limits for each search value are supported;
     *                               defaults to no limit
     * @return string|array          String with replaced values;
     *                               if $string is an array, array of strings
     */
    public static function replace($string, $search, $replace, $limit = -1)
    {
        // without a limit we might as well use the built-in function
        if($limit === -1) return str_replace($search, $replace, $string);

        // if the limit is zero, the result will be no replacements at all
        if($limit === 0) return $string;

        // multiple subjects are run separately through this method
        if(is_array($string)) {
            $result = [];
            foreach($string as $s) {
                $result[] = static::replace($s, $search, $replace, $limit);
            }

            return $result;
        }

        // build an array of replacements
        // we don't use an associative array because otherwise you couldn't
        // replace the same string with different replacements
        $replacements = static::makeReplacements($search, $replace, $limit);

        // run the string and the replacement array through the replacer
        return static::replaceReplacements($string, $replacements);
    }


    /**
     * Generates a replacement array out of dynamic input data
     *
     * @param  string|array $search  Value being searched for (needle)
     * @param  string|array $replace Value to replace matches with
     * @param  int|array    $limit   Maximum possible replacements for each search value;
     *                               multiple limits for each search value are supported;
     *                               defaults to no limit
     * @return array                 List of replacement arrays, each with a
     *                               'search', 'replace' and 'limit' attribute
     */
    public static function makeReplacements($search, $replace, $limit)
    {
        $replacements = [];

        if(is_array($search) && is_array($replace)) {
            foreach($search as $i => $s) {
                // replace with an empty string if no replacement string was defined for this index;
                // behavior is identical to the official PHP str_replace()
                $r = (isset($replace[$i]))? $replace[$i] : '';

                if(is_array($limit)) {
                    // don't apply a limit if no limit was defined for this index
                    $l = (isset($limit[$i]))? $limit[$i] : -1;
                } else {
                    $l = $limit;
                }

                $replacements[] = ['search' => $s, 'replace' => $r, 'limit' => $l];
            }
        } else if(is_array($search) && is_string($replace)) {
            foreach($search as $i => $s) {
                if(is_array($limit)) {
                    // don't apply a limit if no limit was defined for this index
                    $l = (isset($limit[$i]))? $limit[$i] : -1;
                } else {
                    $l = $limit;
                }

                $replacements[] = ['search' => $s, 'replace' => $replace, 'limit' => $l];
            }
        } else if(is_string($search) && is_string($replace) && is_int($limit)) {
            $replacements[] = compact('search', 'replace', 'limit');
        } else {
            throw new Exception('Invalid combination of $search, $replace and $limit params.');
        }

        return $replacements;
    }


    /**
     * Takes a replacement array and processes the replacements
     *
     * @param  string $string       String being replaced on (haystack)
     * @param  array  $replacements Replacement array from Butler::makeReplacements()
     * @return string               String with replaced values
     */
    public static function replaceReplacements($string, $replacements)
    {
        // replace in the order of the replacements
        // behavior is identical to the official PHP str_replace()
        foreach($replacements as $r) {
            if(!is_int($r['limit'])) {
                throw new Exception('Invalid limit "' . $r['limit'] . '".');
            } else if($r['limit'] === -1) {
                // no limit, we don't need our special replacement routine
                $string = str_replace($r['search'], $r['replace'], $string);
            } else if($r['limit'] > 0) {
                // limit given, only replace for $r['limit'] times per replacement
                $pos = -1;
                for($i = 0; $i < $r['limit']; $i++) {
                    $pos = strpos($string, $r['search'], $pos + 1);
                    if(is_int($pos)) {
                        $string = substr_replace($string, $r['replace'], $pos, strlen($r['search']));

                        // adapt $pos to the now changed offset
                        $pos = $pos + strlen($r['replace']) - strlen($r['search']);
                    } else {
                        // no more match in the string
                        break;
                    }
                }
            }
        }

        return $string;
    }


    /**
     * Better alternative for explode()
     * It takes care of removing empty values
     * and it has a built-in way to skip values
     * which are too short.
     *
     * @param  string  $string The string to split
     * @param  string  $separator The string to split by
     * @param  int     $length The min length of values.
     * @return array   An array of found values
     */
    public static function split($string, $separator = ',', $length = 1)
    {

        if(is_array($string)) return $string;

        $string = trim($string, $separator);
        $parts  = explode($separator, $string);
        $out    = array();

        foreach($parts AS $p) {
            $p = trim($p);
            if(static::length($p) > 0 && static::length($p) >= $length) $out[] = $p;
        }

        return $out;
    }


    /**
     * Convert a string to 7-bit ASCII.
     *
     * @param  string  $string
     * @return string
     */
    public static function ascii(string $string): string
    {
        $string  = str_replace(
            array_keys(static::$language),
            array_values(static::$language),
            $string
        );

        $string  = preg_replace(
            array_keys(static::$ascii),
            array_values(static::$ascii),
            $string
        );

        return preg_replace('/[^\x09\x0A\x0D\x20-\x7E]/', '', $string);
    }


   /**
     * Returns the position of a needle in a string
     * if it can be found
     *
     * @param string $string
     * @param string $needle
     * @param bool $caseInsensitive
     * @return int|bool
     */
    public static function position(string $string = null, string $needle, bool $caseInsensitive = false)
    {
        if ($caseInsensitive === true) {
            $string = static::lower($string);
            $needle = static::lower($needle);
        }

        return mb_strpos($string, $needle, 0, 'UTF-8');
    }


    /**
     * Convert a string to a safe version to be used in a URL
     *
     * @param  string  $string The unsafe string
     * @param  string  $separator To be used instead of space and other non-word characters.
     * @return string  The safe string
     */
    public static function slug($string, $separator = null, $allowed = null)
    {
        $separator = $separator !== null ? $separator : '-';
        $allowed   = $allowed   !== null ? $allowed   : 'a-z0-9';

        $string = trim($string);
        $string = static::lower($string);
        $string = static::ascii($string);

        // replace spaces with simple dashes
        $string = preg_replace('![^' . $allowed . ']!i', $separator, $string);

        if(strlen($separator) > 0) {
            // remove double separators
            $string = preg_replace('![' . preg_quote($separator) . ']{2,}!', $separator, $string);
        }

        // trim trailing and leading dashes
        $string = trim($string, $separator);

        // replace slashes with dashes
        $string = str_replace('/', $separator, $string);

        return $string;
    }


    /**
     * Checks if a string starts with the passed needle
     *
     * @param string $string
     * @param string $needle
     * @param bool $caseInsensitive
     * @return bool
     */
    public static function startsWith(string $string = null, string $needle, bool $caseInsensitive = false): bool
    {
        if ($needle === '') {
            return true;
        }

        return static::position($string, $needle, $caseInsensitive) === 0;
    }


    /**
     * Removes all html tags and encoded chars from a string
     *
     * <code>
     *
     * echo str::unhtml('some <em>crazy</em> stuff');
     * // output: some uber crazy stuff
     *
     * </code>
     *
     * @param  string  $string
     * @return string  The html string
     */
    public static function unhtml($string)
    {
        $string = strip_tags($string);
        return html_entity_decode($string, ENT_COMPAT, 'utf-8');
    }
}