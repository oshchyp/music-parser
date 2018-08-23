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

    public $archivePath = '@app/music_files/archives';

    public $domain = '@app/';

    public $fileDownloadPath = '';

    public function getCookiePath()
    {
        return LoginIsraCloud::cookiePath();
    }

    public function getFileDownloadPath(){
       // $path = Yii::getAlias($this->archivePath).'/'.str_replace('.html','',basename($this->domain));
        $path = static::getOrCreateDir(Yii::getAlias($this->archivePath));
        if (is_dir($path) && $this->domain){
            $path .= '/'.str_replace('.html','',basename($this->domain));
        }
        return $this->domain ? $path : null;
    }

}