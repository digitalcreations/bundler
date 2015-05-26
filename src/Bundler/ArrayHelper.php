<?php

namespace DC\Bundler;

class ArrayHelper {
    private function __construct()
    {
    }

    public static function flatten(array $array) {
        $result = array();
        foreach($array as $key=>$value) {
            if(is_array($value)) {
                $result = $result + self::flatten($value);
            }
            else {
                $result[$key] = $value;
            }
        }
        return $result;
    }
} 