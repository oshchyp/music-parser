<?php

namespace app\models;

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

    public static function parseAll()
    {
        $instance = static::getInstance();
        $pagination = ParserPaginationLinks::getInstance()->loadModel();
        if ($pagination->links) {
            foreach ($pagination->links as $k => $url) {
                $instance->links = [];
                $instance->setDomain($url);
                $instance->setFilePath('@app/music_files/json/album_links/page_'.$k.'.json');
                $instance->loadModel();
                $instance->loadPage();
                $instance->parse();
                $instance->saveToJson();
            }
        }
    }
}
