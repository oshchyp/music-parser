<?php
/**
 * Created by PhpStorm.
 * User: programmer_5
 * Date: 06.08.2018
 * Time: 12:11
 */

namespace app\models;


use Yii;
use yii\base\Model;

class ParserAlbumsArchives extends Parser
{

    public $archivePath = 'archives/download';

    public $domain = '';

    public function getCookiePath()
    {
        return LoginIsraCloud::cookiePath();
    }

    public function getFileDownloadPath(){
        $path = Yii::getAlias('@app').'/'.$this->archivePath.'/'.str_replace('.html','',basename($this->domain));
        return static::getOrCreateDir($path);
    }

}