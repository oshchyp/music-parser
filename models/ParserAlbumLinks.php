<?php

namespace app\models;

use app\models\helper\Math;
use Yii;

class ParserAlbumLinks extends Parser
{
    public $filePath = '@app/music_files/json/album_links/links.json';

    public $links = [];



    public function rules()
    {
        return [
            ['links', 'safe'],
        ];
    }

    public function fields()
    {
        return ['links'];
    }

    public function parseLinks($loadPage = true)
    {
        if ($loadPage) {
            $this->loadPage();
        }
        if ($links = $this->findDom('#dle-content .story h2 a')) {
            foreach ($links as $linkObj) {
                $this->links[$linkObj->href] = $linkObj->href;
            }
        }
        //   var_dump($this->links); die();
        return $this;
    }

    public function getPageNumber(){
       // return str_replace();
    }

    public static function pInfo($url, $page)
    {
        $instance = static::getInstance();
        $instance->links = [];
        $instance->setDomain($url);
        $instance->setFilePath('@app/music_files/json/album_links/page_' . $page . '.json');
        $instance->loadModel();
        $instance->loadPage();
        $instance->parse();
        $instance->saveToJson();


        return $instance;
    }

    public static function parseAll()
    {

        $pagination = ParserPaginationLinks::getInstance()->loadModel();
        if ($pagination->links) {
            foreach ($pagination->links as $k => $url) {
                static::pInfo($url,$k);
            }
        }
    }


    public static function partParsing($qPages)
    {
     //   var_dump(static::getLastParsingPage());die();
        $pages = [];
        $pagination = ParserPaginationLinks::getInstance()->loadModel();
        $startPage = static::getLastParsingPage();
        $endPage = $startPage + $qPages;
        if ($pagination->links) {
            $k = 0;
            foreach ($pagination->links as $k => $url) {
//var_dump($k); var_dump(static::getLastParsingPage());
                if ($k >= $startPage && $k < $endPage) {
                    $pages[] = static::pInfo($url, $k);
                }
                if ($k >= $endPage) {
                    break;
                }
            }
            file_put_contents(static::getOrCreateDir(Yii::getAlias(static::$lastParsingFilePath)), $endPage);
        }
        return $pages;
    }
}
