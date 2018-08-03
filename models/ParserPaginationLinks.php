<?php

namespace app\models;

class ParserPaginationLinks extends Parser
{
    public $filePath = 'parseJsonFiles/paginationLinks.json';

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
        if ($links = $this->findDom('#dle-content .nav')) {
            if (!$links = $links[0]->find('a')) {
                return $this;
            }
            $linkLastQ = count($links) - 1;
            if (array_key_exists($linkLastQ, $links)) {
                $pageQ = $links[$linkLastQ]->text();
                $pageQ = (int) preg_replace('/[^0-9]/', '', $pageQ);
                for ($i = 0; $i <= $pageQ; ++$i) {
                    $url = 'https://www.israbox.ch';
                    if ($i > 0) {
                        $url .= '/page/'.$i.'/';
                    }
                    $this->links[$i] = $url;
                }
            }
        }

        return $this;
    }
}
