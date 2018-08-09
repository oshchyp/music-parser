<?php
/**
 * Created by PhpStorm.
 * User: programmer_5
 * Date: 03.08.2018
 * Time: 16:52
 */

include_once '../index.php';

$albumInstance = \app\models\ParserAlbums::getInstance(['domain' => 'https://www.israbox.ch/3137659093-va-nrj-l-ete-des-champions-2018-2018.html']);
$albumInstance->createFilePath();
$albumInstance->loadPage();
$albumInstance->parse();
$albumInstance->saveArchiveSecondThread();
if ($albumInstance->title) {
    $albumInstance->saveToJson();
}









