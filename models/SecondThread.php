<?php
/**
 * Created by PhpStorm.
 * User: programmer_5
 * Date: 10.08.2018
 * Time: 11:22
 */

namespace app\models;


use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Json;

class SecondThread extends Model
{

    public $params = [];

    public $route = '';

    public $outputDir = '@app/music_files/logs/second_thread/output';

    public $tmpDir = '@app/music_files/logs/second_thread/tmp';

    public $lineDir = '@app/music_files/logs/second_thread/line';

    /**
     * generate Process Implementation Logs DIR
     */
    public $prImplLogsDir = '@app/music_files/logs/second_thread/process_implementation_logs';

    public $uniqFileName;


    public function exec(){
     //   $this->sleep();
        $this->startScript();
        exec('php '.Yii::getAlias('@app').'/yii '.$this->route.' '.$this->getParams().' > '.$this->getPath('output').' &');
    }

    public static function execStatic($params=[],$sleep=null){
        $instance = new static();
        if ($params){
            foreach ($params as $attr=>$value){
                $instance->$attr = $value;
            }
        }
        if ($sleep && !$instance->sleep($sleep)){
           return;
        }
        $instance->exec();
        return $instance;
    }

    public function getParams(){
        if ($this->params){
            foreach ($this->params as $k=>$v){
                $this->params[$k] = '"'.$v.'"';
            }
        }
        return implode(' ',$this->params);
    }

    public function getUniqFileName(){
        if ($this->uniqFileName === null){
            $this->uniqFileName = str_replace('/','-',$this->route).'/'.uniqid().'_'.date('Y-m-d_H-i-s').'.txt';
        }
        return $this->uniqFileName;
    }

    public function getPath($attr){
        $attrDir = $attr.'Dir';
        return static::getOrCreatePath($this->$attrDir.'/'.$this->getUniqFileName());
    }

    public function getPrImplLogsContent(){
        $str  = 'Route: '.$this->route;
        $str .= PHP_EOL.'Output: '.$this->getPath('output');
        $str .= PHP_EOL.'Params: '.$this -> getParams();
        $str .=PHP_EOL.PHP_EOL;
        $str .= 'Start: '.date('Y-m-d H:i:s');
        return $str;
    }

    public function sleep($limit=3){
        $path = $this->getPath('tmp');
        $dir = pathinfo($path)['dirname'];
        if (count(FileHelper::findFiles($dir)) > $limit){
            $this->setLine();
            return false;
        }
        return true;
    }

    public function setLine(){
        $path = $this->getPath('line');
        file_put_contents($path,Json::encode(['route'=>$this->route,'params'=>$this->params]));
    }

    public function execLine(){
        $path = $this->getPath('line');
        $dir = pathinfo($path)['dirname'];

        if (is_dir($dir) && $files = FileHelper::findFiles($dir)){

            foreach ($files as $filePath){
              if (is_file($filePath)) {
                  $this->execFromFile($filePath);
              }

            }
        }
    }

    public function execFromFile($filePath){
        $fileContent = file_get_contents($filePath);
        $fileContentJson = Json::decode($fileContent,true);

        static::execStatic(
            [
                'route'=>ArrayHelper::getValue($fileContentJson,'route',''),
                'params'=>ArrayHelper::getValue($fileContentJson,'params','')
            ],
            3);
        unlink($filePath);
    }

    public function startScript(){
        $logContent = $this->getPrImplLogsContent();
        $pathArray = [
            $this->getPath('prImplLogs'),
            $this->getPath('tmp')
        ];
        foreach ($pathArray as $path){
            $this->params[] = $path;
            file_put_contents($path,$logContent);
        }
    }

    public static function endScript($logPath='',$tmpPath=''){
        if (is_file($logPath)){
            $logContent = file_get_contents($logPath).PHP_EOL.'End: '.date('Y-m-d H_i_s');
            file_put_contents($logPath,$logContent);
        }
        if (is_file($tmpPath)){
            unlink($tmpPath);
        }
    }

    public static function execLines($route){
        $model = new static(['route'=>$route]);
        $model->execLine();
    }

    public static function getOrCreatePath($path,$pathWithFile=true){
        $path = Yii::getAlias($path);
        $dir = $pathWithFile ? pathinfo($path)['dirname'] : $path;
        if (!is_dir($dir)) {
            FileHelper::createDirectory($dir);
        }

        return $path;
    }


}