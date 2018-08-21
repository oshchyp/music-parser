<?php
/**
 * Created by PhpStorm.
 * User: programmer_5
 * Date: 09.08.2018
 * Time: 16:20
 */

namespace app\commands;

use app\models\ArchiveHandling;
use app\models\LoginIsraCloud;
use app\models\Parser;
use app\models\ParserAlbumLinks;
use app\models\ParserAlbums;
use app\models\ParserAlbumsArchives;
use app\models\ParserCategories;
use app\models\ParserPaginationLinks;
use app\models\SecondThread;
use yii\console\Controller;
use yii\console\ExitCode;

class ParserController extends Controller
{

    public function actionIndex($message){
        echo $message . "\n";
        return ExitCode::OK;
    }

    public function actionCategories(){
        ParserCategories::getInstance()->loadPage()->parse()->convertCategories()->saveToJson()->saveToDB();
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
        (new LoginIsraCloud) -> login();
        ParserAlbums::parseAll();
        return ExitCode::OK;
    }

    public function actionPartParsing($logPath='',$tmpPath=''){
        (new LoginIsraCloud)->login();
        $links = ParserAlbumLinks::partParsing(4);
        ParserAlbums::partParsing($links);

    }

    public function actionAlbumsArchives($albumFilePath,$logPath,$tmpPath){
        $model = ParserAlbums::getInstance(['filePath'=>$albumFilePath])->loadModel();
        $model->saveArchive();
        $model-> saveToJson();

        SecondThread::execStatic(['route'=>'parser/albums-archives-upload','params'=>[$albumFilePath]],2);

        SecondThread::endScript($logPath,$tmpPath);
        SecondThread::execLines('parser/albums-archives');
        return ExitCode::OK;
    }

    public function actionAlbumsArchivesUpload($albumFilePath,$logPath='',$tmpPath=''){

        $model = ParserAlbums::getInstance(['filePath'=>$albumFilePath])->loadModel();
        $model->uploadArchive(true);
        $model-> saveToJson();
        $model->saveToDb();
        SecondThread::endScript($logPath,$tmpPath);

        SecondThread::execLines('parser/albums-archives-upload');

        return ExitCode::OK;
    }

  //  public function action

    public function actionDebug(){
       // phpinfo();die();
//        (new LoginIsraCloud) -> login();
//        $instance = ParserAlbums::pAlbum('https://www.israbox.ch/3136455199-till-bronner-till-bronner-2cd-deluxe-edition-2012.html');
//        $instance->saveToDb();

        $instance = ArchiveHandling::getInstance(['filePath' => '@app/music_files/archives/test.rar']);
        $instance->unarchive();
        $instance->handlingTmpDir();
        $instance->archive();

        return ExitCode::OK;
    }

    public function actionTest($logPath,$tmpPath)
    {
        for($i=0; $i<30; $i++){
            echo $i.PHP_EOL;
            sleep(1);
        }
        SecondThread::endScript($logPath,$tmpPath);
      //  SecondThread::execLines('parser/test');
        return ExitCode::OK;
    }


}