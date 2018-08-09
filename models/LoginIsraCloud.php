<?php
/**
 * Created by PhpStorm.
 * User: programmer_5
 * Date: 06.08.2018
 * Time: 10:40
 */

namespace app\models;


use Yii;
use yii\base\Model;

class LoginIsraCloud extends Parser
{

    public $login = 'minimalistica';

    public $password = 'dario234221';

    public $domain = 'https://isra.cloud/';

    public static $cookiePath = '@app/music_files/cookies/isra-cloud.txt';

    public function login(){
         $ch = curl_init('https://isra.cloud/');
         curl_setopt ($ch, CURLOPT_POST, true);
         curl_setopt ($ch, CURLOPT_POSTFIELDS, $this->requestData());
         curl_setopt($ch, CURLOPT_COOKIEJAR, static::cookiePath());
         curl_setopt($ch, CURLOPT_COOKIEFILE, static::cookiePath());
         curl_setopt($ch, CURLOPT_HEADER, false);
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
         curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
         $response = curl_exec($ch);
         $responseCode = curl_getinfo ( $ch ,CURLINFO_RESPONSE_CODE);
         curl_close($ch);
         return static::testLogin();
    }

    public function requestData(){
        return [
            'login' => $this->login,
            'password' => $this->password,
            'op' => 'login',
            'rand' => '',
            'redirect' => 'https://isra.cloud/'
        ];
    }

    public static function testLogin(){
        $instance = static::getInstance();
        $instance ->loadPage();
        return $instance->pageObject && $instance->pageObject->find('a[href="https://isra.cloud/?op=logout"]',0) ? true : false;
    }

    public static function cookiePath(){
        $path = Yii::getAlias(static::$cookiePath);
        $path = Parser::getOrCreateDir($path);
        if (!is_file($path)){
            file_put_contents($path,'');
        }
        return $path;
    }

    public function getCookiePath()
    {
        return static::cookiePath();
    }

}