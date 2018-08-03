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

    public $filePath = 'parseJsonFiles/p.json';

    public $pageObject;

    public $logsPath = 'logs/error';

    protected function _curl()
    {
        $ch = curl_init();

        // set url
        curl_setopt($ch, CURLOPT_URL, $this->getUrl());

        //return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // $output contains the output string
        $output = curl_exec($ch);

        // close curl resource to free up system resources
        curl_close($ch);
        if (!$output) {
            $this->loging();
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
            $url .= '/'.$this->url;
        }

        return $url;
    }

    public function getLogPath()
    {
        $path = Yii::getAlias('@app').'/'.$this->logsPath.'/'.str_replace('/', '-', $this->filePath);

        return $this->getOrCreateDir($path);
    }

    public function getFilePath()
    {
        return $this->getOrCreateDir(Yii::getAlias('@app').'/'.$this->filePath);
    }

    public function loging()
    {
        $arr = [
            'domain' => $this->domain,
            'url' => $this->url,
            'filePath' => $this->filePath,
            'model' => static::className(),
        ];
        $json = Json::encode($this->getLogPath());
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
