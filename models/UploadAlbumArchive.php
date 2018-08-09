<?php
/**
 * Created by PhpStorm.
 * User: programmer_5
 * Date: 06.08.2018
 * Time: 17:52.
 */

namespace app\models;

use Yii;
use yii\base\Model;
use yii\helpers\Json;

class UploadAlbumArchive extends Model
{
    public $filePath = '';

    public $token = '1000019_PcpB45eRcwPftsmmSqph';

    public $folderId = null;

    public $apiUrl = 'https://api.fc.4crp.com';

    private function _curl($url, $data = [], $headers = [])
    {
        $options = [
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_URL => $url,
        ];
        if ($data) {
            $options[CURLOPT_POSTFIELDS] = $data;
        }
        if ($headers) {
            $options[CURLOPT_HTTPHEADER] = $headers;
        }
        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        //dump($response, 1);
        curl_close($ch);

        return $response;
    }

    public function getUploadLink()
    {
        $data = [
            'file_name' => basename($this->getFilePath()),
            'file_size' => filesize($this->getFilePath()),
            'folder_id' => $this->folderId,
        ];
        $dataJson = Json::encode($data);
        $headers = [
            'Content-Type: application/json',
            'Content-Length: '.strlen($dataJson),
            'Api-Token: '.$this->token,
        ];
        $response = $this->_curl($this->apiUrl.'/upldreq', $dataJson, $headers);
        $responseArray = Json::decode($response, true);
        if (array_key_exists('link', $responseArray)) {
            return $responseArray['link'];
        }

        return null;
    }

    public function getPublicLink($url)
    {
        $data = array('file' => file_get_contents($this->getFilePath()));
        $headers = [
            'Content-Type: multipart/form-data',
            'Api-Token: '.$this->token,
        ];
        $response = $this->_curl($url, $data, $headers);
        $responseArray = Json::decode($response, true);
        if (array_key_exists('uid', $responseArray)) {
            return 'https://fc.4crp.com/d/'.$responseArray['uid'];
        }

        return null;
    }

    public function getFilePath()
    {
        return strstr($this->filePath, Yii::getAlias('@app')) ? $this->filePath : Yii::getAlias('@app').'/'.$this->filePath;
    }

    public function upload()
    {
        $uploadLink = $this->getUploadLink();
        if ($uploadLink) {
            return $this->getPublicLink('https://'.$uploadLink);
        }

        return null;
    }
}
