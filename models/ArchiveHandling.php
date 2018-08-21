<?php
/**
 * Created by PhpStorm.
 * User: programmer_5
 * Date: 21.08.2018
 * Time: 13:22
 */

namespace app\models;


use Yii;
use yii\base\Model;
use yii\helpers\FileHelper;

class ArchiveHandling extends Model
{

    public $filePath;

    public $newFilePath;

    public $tmpDir = '@app/music_files/archive_handling/tmp';

    public $tmpDirArchive;


    public function getTmpDirArchive(){
        if (!$this->tmpDirArchive){
            $this->tmpDirArchive = $this->tmpDir.'/'.uniqid(time());
        }
        return $this->tmpDirArchive;
    }

    public function getNewFilePath(){
        return $this->newFilePath ? $this->newFilePath : $this->filePath;
    }

    public static function getInstance($params = [])
    {
        $instance = new static();
        if ($params) {
            foreach ($params as $attr => $value) {
                $instance->$attr = $value;
            }
        }

        return $instance;
    }

    public static function getOrCreateDir($path,$withFile=true)
    {
        $dir = Yii::getAlias($path);
        if ($withFile){
            $dir = pathinfo($path)['dirname'];
        }
        if (!is_dir($dir)) {
            FileHelper::createDirectory($dir);
        }

        return Yii::getAlias($path);
    }

    public static function filterName($name){
        $allowedTypes = ['mp3','flac'];
        $pathinfo = pathinfo($name);
        $extension = array_key_exists('extension',$pathinfo) ? mb_strtolower($pathinfo['extension']) : '';
        return in_array($extension,$allowedTypes);
    }

    public function unarchive(){
        switch (mb_strtolower(pathinfo($this->filePath)['extension'])){
            case 'rar':
                $this->unrar();
                break;
            case 'zip':
                $this->unzip();
                break;

        }
        return $this;
    }

    public function archive(){
        $pathInfo = pathinfo($this->getNewFilePath());
        $newFilePath = array_key_exists('filename',$pathInfo) ? $pathInfo['filename'].'.zip' : uniqid(time()).'.zip';
        $newFilePath = $pathInfo['dirname'] .'/'.$newFilePath;
        $dir = static::getOrCreateDir($this->getTmpDirArchive(), false);
        if ($file = FileHelper::findFiles($dir)) {
            $zipObject = new \ZipArchive;
            if($zipObject->open(Yii::getAlias($newFilePath), \ZipArchive::CREATE) === true) {
                foreach ($file as $filePath) {
                    $zipObject->addFile($filePath,basename($filePath));
                }
                $this->newFilePath = $newFilePath;
                $zipObject->close();
            }
        }

    }

    public function unrar(){
        if (is_file(Yii::getAlias($this->filePath)) && $rarObject = \RarArchive::open(Yii::getAlias($this->filePath))){
            $entries = $rarObject->getEntries();
            foreach ($entries as $obj){
                if (static::filterName($obj->getName())) {
                    $obj->extract(static::getOrCreateDir($this->getTmpDirArchive(), false));
                }
            }
            $this->archive();
            $rarObject->close();
        }

        return true;
    }

    public function unzip(){
        $zipObject = new \ZipArchive;
        if (is_file(Yii::getAlias($this->filePath)) && $zipObject->open(Yii::getAlias($this->filePath))){
            for ($i = 0; $i < $zipObject->numFiles; $i++) {
                if (!$filename = static::filterName($zipObject->getNameIndex($i))) {
                    $zipObject->deleteIndex($i);
                }
            }
        }
        $zipObject->close();
    }


    function __destruct()
    {
        if (is_dir($this->getTmpDirArchive())){
            FileHelper::removeDirectory($this->getTmpDirArchive());
        }

        if ($this->getNewFilePath() !== $this->filePath && is_file(Yii::getAlias($this->filePath)) && is_file(Yii::getAlias($this->getNewFilePath()))){
            unlink(Yii::getAlias($this->filePath));
        }
    }

}