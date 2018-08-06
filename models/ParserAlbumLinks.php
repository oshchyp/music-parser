<?php

namespace app\models;

class ParserAlbumLinks extends Parser
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
        $instance = static::getInstance();
        $pagination = ParserPaginationLinks::getInstance()->loadModel();
        if ($pagination->links) {
            foreach ($pagination->links as $k => $url) {
                $instance->links = [];
                $instance->setDomain($url);
                $instance->setFilePath('parseJsonFiles/albumLinks/page_'.$k.'.json');
                $instance->loadModel();
                $instance->loadPage();
                $instance->parse();
                $instance->saveToJson();
            }
        }
    }
}
