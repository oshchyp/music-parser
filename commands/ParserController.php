<?php
/**
 * Created by PhpStorm.
 * User: programmer_5
 * Date: 09.08.2018
 * Time: 16:20
 */

namespace app\commands;

use app\models\LoginIsraCloud;
use app\models\ParserAlbumLinks;
use app\models\ParserAlbums;
use app\models\ParserAlbumsArchives;
use app\models\ParserPaginationLinks;
use yii\console\Controller;
use yii\console\ExitCode;

class ParserController extends Controller
{

    public function actionIndex($message){
        echo $message . "\n";
        return ExitCode::OK;
    }

    public function actionPaginationLinks(){
        ParserPaginationLinks::getInstance()->loadPage()->parse()->saveToJson();
        return ExitCode::OK;
    }

    public function actionAlbumsLinks(){
        ParserAlbumLinks::parseAll();
        return ExitCode::OK;
    }

    public function actionAlbums(){

        $albumInstance = ParserAlbums::getInstance(['domain' => 'https://www.israbox.ch/3137659093-va-nrj-l-ete-des-champions-2018-2018.html']);
        $albumInstance->createFilePath();
        $albumInstance->loadPage();
        $albumInstance->parse();
        $albumInstance->saveArchiveSecondThread();
        if ($albumInstance->title) {
            $albumInstance->saveToJson();
        }
        return ExitCode::OK;
    }

    public function actionAlbumsArchives($url){
        (new LoginIsraCloud())->login();
        $archiveModel =  ParserAlbumsArchives::getInstance(['domain' => $url]);
        $archiveModel -> loadPage();
        return ExitCode::OK;
    }

}