<?php

namespace app\models;

class ParseAlbumLinks extends Parser
{
    public $filePath = 'parseJsonFiles/albumLinks.json';

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
        if ($links = $this->findDom('#dle-content .story a')) {
            foreach ($links as $linkObj) {
                $this->links[$linkObj->href] = $linkObj->href;
            }
        }

        return $this;
    }

    public static function parseAll()
    {
        $instanse = static::getInstance()->loadModel();
        $pagination = ParserPaginationLinks::getInstance()->loadModel();
        if ($pagination->links) {
            foreach ($pagination->links as $k => $url) {
                $instanse->links = [];
                $instanse->setDomain($url);
                $instanse->setFilePath('parseJsonFiles/albumLinks/page_'.$k.'.json');
                $instanse->loadPage()->parseLinks()->saveToJson();
            }
        }
    }
}
