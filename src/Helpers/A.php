<?php

namespace Fundevogel\Pcbis\Helpers;

use Exception;


/**
 * The `A` class provides a set of handy methods
 * to simplify array handling and make it more
 * consistent. The class contains methods for
 * fetching elements from arrays, merging and
 * sorting or shuffling arrays.
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class A
{
    /**
     * Better alternative for implode()
     *
     * @param string  $value The value to join
     * @param string  $separator The string to join by
     * @param int     $length The min length of values.
     *
     * @return string An array of found values
     */
    public static function join($value, $separator = ', '): string
    {
        if (is_string($value) === true) {
            return $value;
        }

        return implode($separator, $value);
    }


    /**
     * Update an array with a second array
     * The second array can contain callbacks as values,
     * which will get the original values as argument
     *
     * @param array $array
     * @param array $update
     */
    public static function update($array, $update): array
    {
        foreach($update as $key => $value) {
            $array[$key] = $value;
        }

        return $array;
    }


    /**
     * Checks for missing elements in an array
     *
     * This is very handy to check for missing
     * user values in a request for example.
     *
     * <code>
     *
     * $array = [
     *   'cat' => 'miao',
     *   'dog' => 'wuff',
     *   'bird' => 'tweet',
     *   'hippo' => null
     * ];
     *
     * $required = ['cat', 'elephant', 'hippo'];
     *
     * $missing = a::missing($array, $required);
     * // missing: ['elephant', 'hippo'];
     *
     * </code>
     *
     * @param   array  $array The source array
     * @param   array  $required An array of required keys
     * @return  array  An array of missing fields. If this is empty, nothing is missing.
     */
    public static function missing($array, $required = [])
    {
        $missing = [];

        foreach($required as $r) {
            if(!isset($array[$r])) $missing[] = $r;
        }

        return $missing;
    }


    /**
     * Gets an element of an array by key
     *
     * <code>
     *
     * $array = array(
     *   'cat'  => 'miao',
     *   'dog'  => 'wuff',
     *   'bird' => 'tweet'
     * );
     *
     * echo a::get($array, 'cat');
     * // output: 'miao'
     *
     * echo a::get($array, 'elephant', 'shut up');
     * // output: 'shut up'
     *
     * $catAndDog = a::get(array('cat', 'dog'));
     * // result: array(
     * //   'cat' => 'miao',
     * //   'dog' => 'wuff'
     * // );
     *
     * </code>
     *
     * @param   array  $array The source array
     * @param   mixed  $key The key to look for
     * @param   mixed  $default Optional default value, which should be returned if no element has been found
     * @return  mixed
     */
    public static function get($array, $key, $default = null)
    {
        // get an array of keys
        if(is_array($key)) {
            $result = array();
            foreach($key as $k) $result[$k] = static::get($array, $k);
            return $result;

        // get a single
        } else if(isset($array[$key])) {
            return $array[$key];

        // return the entire array if the key is null
        } else if(is_null($key)) {
            return $array;

        // get the default value if nothing else worked out
        } else {
            return $default;
        }
    }


    /**
     * Sorts a multi-dimensional array by a certain column
     *
     * <code>
     *
     * $array[0] = array(
     *   'id' => 1,
     *   'username' => 'bastian',
     * );
     *
     * $array[1] = array(
     *   'id' => 2,
     *   'username' => 'peter',
     * );
     *
     * $array[3] = array(
     *   'id' => 3,
     *   'username' => 'john',
     * );
     *
     * $sorted = a::sort($array, 'username ASC');
     * // Array
     * // (
     * //      [0] => Array
     * //          (
     * //              [id] => 1
     * //              [username] => bastian
     * //          )
     * //      [1] => Array
     * //          (
     * //              [id] => 3
     * //              [username] => john
     * //          )
     * //      [2] => Array
     * //          (
     * //              [id] => 2
     * //              [username] => peter
     * //          )
     * // )
     *
     * </code>
     *
     * @param   array   $array The source array
     * @param   string  $field The name of the column
     * @param   string  $direction desc (descending) or asc (ascending)
     * @param   int   $method A PHP sort method flag or 'natural' for natural sorting, which is not supported in PHP by sort flags
     * @return  array   The sorted array
     */
    public static function sort($array, $field, $direction = 'desc', $method = SORT_REGULAR)
    {
        $direction = strtolower($direction) == 'desc' ? SORT_DESC : SORT_ASC;
        $helper    = array();
        $result    = array();

        // build the helper array
        foreach($array as $key => $row) $helper[$key] = $row[$field];

        // natural sorting
        if($method === SORT_NATURAL) {
            natsort($helper);
            if($direction === SORT_DESC) $helper = array_reverse($helper);
        } else if($direction === SORT_DESC) {
            arsort($helper, $method);
        } else {
            asort($helper, $method);
        }

        // rebuild the original array
        foreach($helper as $key => $val) $result[$key] = $array[$key];

        return $result;
    }


    /**
     * Returns the first element of an array
     *
     * I always have to lookup the names of that function
     * so I decided to make this shortcut which is
     * easier to remember.
     *
     * <code>
     *
     * $array = array(
     *   'cat',
     *   'dog',
     *   'bird',
     * );
     *
     * $first = a::first($array);
     * // first: 'cat'
     *
     * </code>
     *
     * @param   array  $array The source array
     * @return  mixed  The first element
     */
    public static function first($array)
    {
        return array_shift($array);
    }


    /**
     * Returns the last element of an array
     *
     * I always have to lookup the names of that function
     * so I decided to make this shortcut which is
     * easier to remember.
     *
     * <code>
     *
     * $array = array(
     *   'cat',
     *   'dog',
     *   'bird',
     * );
     *
     * $last = a::last($array);
     * // first: 'bird'
     *
     * </code>
     *
     * @param   array  $array The source array
     * @return  mixed  The last element
     */
    public static function last($array) {
        return array_pop($array);
    }
}
