<?php

/**
 *
 * @author huyi
 */
class Tool_Array {

    /**
     * Retrieves muliple single-key values from a list of arrays.
     * @see http://kohanaframework.org/3.0/guide/api/Arr#pluck
     * @param array $array
     * @param string $key
     * @return array
     */
    public static function pluck($array, $key) {
        $values = array();

        foreach ($array as $row) {
            if (array_key_exists($key, $row)) {
                // Found a value in this row
                $values[] = $row[$key];
            }
        }

        return $values;
    }

}
