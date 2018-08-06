<?php

function dump($var, $kill = null)
{
   // echo '<pre>';
    var_dump($var);
  //  echo '</pre>';

    if ($kill) {
        die();
    }
}

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');
define('ROOTDIR', __DIR__);

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/config/Yii/config.php';
(new yii\web\Application($config));

//<form method="POST" action="https://isra.cloud/" name="FL">
//    <input type="hidden" name="op" value="login">
//    <input type="hidden" name="rand" value="">
//    <input type="hidden" name="redirect" value="https://isra.cloud/">
//    <table style="width: 500px">
//        <tbody><tr>
//            <td>Username:</td><td><input type="text" name="login" value="" class="myForm"></td>
//        </tr>
//        <tr>
//            <td>Password:</td><td><input type="password" name="password" class="myForm"></td>
//        </tr>
//
//        </tbody></table>
//
//    <br>
//    <input type="submit" value="Submit">
//    <br>
//    <br>
//</form>


