<?php
/**
 * Created by PhpStorm.
 * User: programmer_5
 * Date: 17.08.2018
 * Time: 14:20
 */

namespace app\models\helper;


class Math
{

    public static function multiplicityNumber($a,$b)
    {
        return $a % $b == 0;
    }

}