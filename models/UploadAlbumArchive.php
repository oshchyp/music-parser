<?php
/**
 * Created by PhpStorm.
 * User: programmer_5
 * Date: 06.08.2018
 * Time: 17:52
 */

namespace app\models;


use yii\base\Model;

class UploadAlbumArchive extends Model
{

    public $filePath = '';

    public $token = '1000019_PcpB45eRcwPftsmmSqph';

    public $folderId = 1;

    public function upload(){
        $name = basename($this->filePath);
        $data = array('file_name' => $name,
            'file_size' => filesize($this->filePath),
            'folder_id' => $this -> folderId, );
        $data_string = json_encode($data);

        $curl = curl_init();
        curl_setopt_array($curl, array(
            //CURLOPT_VERBOSE => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data_string,
            CURLOPT_URL => $this->api_url.'/upldreq',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Content-Length: '.strlen($data_string),
                'Api-Token: '.$this->api_token,
            ), ));
        $response = curl_exec($curl);
        // var_dump($response);
        // die();
        // Извлечение пути для загрузки и подготовка файла
        $upldreq_url = json_decode($response)->link;
        $data = array('file' => file_get_contents($filepath));
        // Загрузка файла в виртуальную файловую систему
        curl_setopt_array($curl, array(
            //CURLOPT_VERBOSE => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_URL => 'https://'.$upldreq_url,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: multipart/form-data',
                'Api-Token: '.$this->api_token,
            ), ));
        $response = curl_exec($curl);
        curl_close($curl);
        // формируем публичную ссылку на скачивание и возвращаем ее
        $public_url = 'https://fc.4crp.com/d/'.json_decode($response)->uid;

        return $public_url;
    }

}