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

//final class PhoneTypes extends App\Classes\Sistema\Base\AbstractEnum
//{
//    const Undefined = 0;
//    const Mobile = 1;
//    const Work = 2;
//    const Home = 3;
//}