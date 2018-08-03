<?php

function dump($var, $kill = null)
{
    echo '<pre>';
    var_dump($var);
    echo '</pre>';

    if ($kill) {
        die();
    }
}

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');
define('ROOTDIR', __DIR__);

require __DIR__.'/vendor/autoload.php';
require __DIR__.'/vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__.'/config/Yii/config.php';
(new yii\web\Application($config));
