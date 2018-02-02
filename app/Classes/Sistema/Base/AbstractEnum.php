<?php

namespace App\Classes\Sistema\Base;

/**
 * Clase abstracta para enums
 */
abstract class AbstractEnum {
    // Método getKeys
    static function getKeys(){
        $class = new ReflectionClass(get_called_class());
        return array_keys($class->getConstants());
    }
}