<?php

namespace Trax\XapiValidation\Traits;

trait CompareStatements
{
    /**
     * Data properties that should be ignore during comparison.
     *
     * @var array
     */
    protected static $ignore = [
        'id',
        'authority',
        'stored',
        'timestamp',
        'version',
        'definition',
        'display',
        'member',
        'attachments',
    ];
    
    /**
     * Compare 2 xAPI data sources.
     *
     * @param  mixed  $source1
     * @param  mixed  $source2
     * @return bool
     */
    public static function compare($source1, $source2): bool
    {
        return self::compareObjects($source1, $source2);
    }
    
    /**
     * Compare 2 items, without knowing their type.
     *
     * @param  mixed  $item1
     * @param  mixed  $item2
     * @return bool
     */
    protected static function compareItems($item1, $item2): bool
    {
        if (is_object($item1) && is_object($item2)) {
            return self::compareObjects($item1, $item2);
        }
        if (is_array($item1) && is_array($item2)) {
            return self::compareArrays($item1, $item2);
        }
        return ($item1 === $item2);
    }
    
    /**
     * Compare 2 objects.
     *
     * @param  \stdClass  $object1
     * @param  \stdClass  $object2
     * @return bool
     */
    protected static function compareObjects(\stdClass $object1, \stdClass $object2): bool
    {
        $props = get_object_vars($object1);
        foreach ($props as $key => $val) {
            //
            // Ignore some props.
            if (in_array($key, self::$ignore)) {
                continue;
            }
            
            // Missing prop in $object2.
            if (!isset($object2->$key)) {
                return false;
            }
        }

        $props = get_object_vars($object2);
        foreach ($props as $key => $val) {
            //
            // Ignore some props.
            if (in_array($key, self::$ignore)) {
                continue;
            }
            
            // Missing prop in $object1.
            if (!isset($object1->$key)) {
                return false;
            }
            
            // Compare values.
            $same = self::compareItems($object1->$key, $val);
            if (!$same) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Compare 2 arrays.
     *
     * @param  array  $array1
     * @param  array  $array2
     * @return bool
     */
    protected static function compareArrays(array $array1, array $array2): bool
    {
        // Different size.
        if (count($array1) != count($array2)) {
            return false;
        }
        
        // Sort them.
        asort($array2);
        asort($array1);
        
        // Compare them.
        foreach ($array2 as $key => $val) {
            $same = self::compareItems($array1[$key], $val);
            if (!$same) {
                return false;
            }
        }
        return true;
    }
}
