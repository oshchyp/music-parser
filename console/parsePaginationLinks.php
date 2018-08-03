<?php
/**
 * Created by PhpStorm.
 * User: programmer_5
 * Date: 03.08.2018
 * Time: 16:25
 */

include_once '../index.php';

$model = \app\models\ParserPaginationLinks::getInstance();

$model->loadPage();

$model -> parse();

$model->saveToJson();