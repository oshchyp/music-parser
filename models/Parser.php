<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\helpers\Json;
use yii\helpers\FileHelper;
use Sunra\PhpSimple\HtmlDomParser;

class Parser extends Model
{
    public $domain = 'https://www.israbox.ch';

    public $url;

    public $filePath;

    public $pageObject;

    public $logPath = '@app/music_files/logs/parser';

    //public $logsPath = 'logs/p.json';

    public static $qReq = 0;


    protected function _curl()
    {
        static::$qReq++;
        $file = $this->getFileDownloadPath() ? fopen($this->getFileDownloadPath(),'w+') : null;
        $ch = curl_init();
        $options = [
            CURLOPT_URL => $this->getUrl(),
            CURLOPT_RETURNTRANSFER => 1,
        ];
        if ($this->getCookiePath()) {
            $options +=[
                CURLOPT_COOKIEJAR => $this->getCookiePath(),
                CURLOPT_COOKIEFILE => $this->getCookiePath(),
            ];
        }

        if ($file) {
            $options +=[
                CURLOPT_TIMEOUT => 600,
                CURLOPT_FILE => $file,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYHOST => '0',
                CURLOPT_SSL_VERIFYPEER => '0',
                CURLOPT_VERBOSE => true,
            ];
       }
        curl_setopt_array($ch, $options);
        $output = curl_exec($ch);
        $responseCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        if ($file) {
            fclose($file);
        }
        if ($responseCode !== 200) {
            $this->setLogs();
        }

        return $output;
    }


    public function loadPage()
    {
        $html = $this->_curl();
        $this->pageObject = HtmlDomParser::str_get_html($html);

        return $this;
    }

    public function getUrl()
    {
        $url = $this->domain;
        if ($this->url) {
            $url .= '/' . $this->url;
        }

        return $url;
    }

    public function getLogPath()
    {
        $path = Yii::getAlias($this->logPath) . '/' . str_replace('app\\models\\','',static::className()).'/'.str_replace(['https://','/'],['','-'],$this->getUrl());

        return $this->getOrCreateDir($path);
    }

    public function getFilePath()
    {
        return $this->getOrCreateDir(Yii::getAlias($this->filePath));
    }

    public function getCookiePath()
    {
        return null;
    }

    public function getFileDownloadPath()
    {
        return null;
    }

    public function setLogs()
    {
        $arr = [
            'domain' => $this->domain,
            'url' => $this->url,
            'filePath' => $this->filePath,
            'model' => static::className(),
        ];
        $json = Json::encode($arr);
        file_put_contents($this->getLogPath(), $json);
    }

    public function findDom($selector)
    {
        return $this->pageObject ? $this->pageObject->find($selector) : null;
    }

    public function saveToJson($fields = [])
    {
        $jsonString = Json::encode($this->toArray($fields));
        file_put_contents($this->getFilePath(), $jsonString);

        return $this;
    }

    public function loadModel()
    {
        $jsonString = is_file($this->getFilePath()) ? file_get_contents($this->getFilePath()) : null;
        $loadArray = $jsonString ? Json::decode($jsonString) : [];
        $this->attributes = $loadArray;

        return $this;
    }

    public static function getOrCreateDir($path)
    {
        if (!is_dir(pathinfo($path)['dirname'])) {
            FileHelper::createDirectory(pathinfo($path)['dirname']);
        }

        return $path;
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

    public function parse()
    {
        $methods = get_class_methods($this);
        foreach ($methods as $v) {
            if (strstr($v, 'parse') && $v !== 'parse' && $v !== 'parseAll' && $this->pageObject) {
                $this->$v();
            }
        }
        return $this;
    }

    /**
     * Set the value of domain.
     *
     * @return self
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * Set the value of url.
     *
     * @return self
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Set the value of filePath.
     *
     * @return self
     */
    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;

        return $this;
    }
}
