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
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

class UploadAlbumArchive extends Model
{
    public $filePath = '';

    public $token = '1000019_PcpB45eRcwPftsmmSqph';

    public $folderId = null;

    public $apiUrl = 'https://api.fc.4crp.com';

    private function _curl($url, $data = [], $headers = [], $post = true)
    {
        $options = [
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => $post,
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
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($responseCode !== 200) {
            $response=null;
        }
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
            'Content-Length: ' . strlen($dataJson),
            'Api-Token: ' . $this->token,
        ];
        $response = $this->_curl($this->apiUrl . '/upldreq', $dataJson, $headers);

        if ($response) {
            $responseArray = Json::decode($response, true);
            if (array_key_exists('link', $responseArray)) {
                return $responseArray['link'];
            }
        }

        return null;
    }

    public function getPublicLink($url)
    {
        ini_set('memory_limit', '500M');
        $data = [
            'file' => curl_file_create($this->getFilePath(), mime_content_type($this->getFilePath()), basename($this->getFilePath()))
        ];
        $headers = [
            'Content-Type: multipart/form-data',
            'Api-Token: ' . $this->token,
        ];
        $response = $this->_curl($url, $data, $headers);

        if ($response) {
            $responseArray = Json::decode($response, true);
            if (array_key_exists('uid', $responseArray)) {
                return 'https://fc.4crp.com/d/' . $responseArray['uid'];
            }
        }

        return null;
    }

    public function getFilePath()
    {
        return Yii::getAlias($this->filePath);
    }

    public function upload()
    {

        if (is_file($this -> getFilePath()) && $uploadLink = $this->getUploadLink()) {
            return $this->getPublicLink('https://' . $uploadLink);
        }

        return null;
    }

    public function deleteLocalArchive()
    {
        if (is_file($this->getFilePath())) {
            unlink($this->getFilePath());
        }
    }

    public function getRootDirInfo()
    {
        $headers = [
            'Content-Type: multipart/form-data',
            'Api-Token: ' . $this->token,
        ];
        $infoJson = $this->_curl('https://api.fc.4crp.com/fs', [], $headers, false);
        $infoArray = $infoJson ? Json::decode($infoJson) : [];
        return ArrayHelper::getValue($infoArray, 'items', []);
    }

    public function fileExist($url)
    {
        $files = $this->getRootDirInfo();
        if ($url && $files) {
            $uid = basename($url);
            foreach ($files as $fileInfo) {
                if ($fileInfo['uid'] == $uid) {
                    return true;
                }
            }
        }
        return false;
    }

}
