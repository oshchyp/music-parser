<?php
/**
 * Created by PhpStorm.
 * User: programmer_5
 * Date: 03.08.2018
 * Time: 11:18
 */

namespace app\models;


use Sunra\PhpSimple\HtmlDomParser;
use Yii;
use yii\helpers\FileHelper;

class ParserAlbums extends Parser
{

    public $categories;

    public $title;

    public $artist;

    public $imageLink;

    public $year_of_release;

    public $tracklist;

    public $description;

    public $label;

    public $quality;

    public $total_time;

    public $total_size;

    public $download_link_donor;

    public $web_site;

    public $genre;

    public $content;

    public $archivePath;

    private $_descriptionObject;

    private $_descriptionHtml;

    public function rules()
    {
        return [
            [['archivePath', 'domain', 'categories', 'title', 'artist', 'imageLink', 'year_of_release', 'tracklist', 'description', 'label', 'quality', 'total_time', 'total_size', 'download_link_donor', 'web_site', 'genre', 'content'], 'safe']
        ];
    }

    public function fields()
    {
        $fields = parent::fields();
        unset($fields['domain'], $fields['url'], $fields['filePath'], $fields['filePath'], $fields['pageObject'], $fields['logsPath']);
        return $fields;
    }

    public function parseSpans()
    {
        if (!$this->pageObject)
            return $this;
        $spans = [
            /////// 0 => itemprop="name"   1 =>  title attribute   2 => attribute dom object
            //  ['name' , 'title'],
            ['author', 'artist'],
            ['releasedEvent', 'year_of_release'],
            ['producer', 'label'],
            ['genre', 'genre'],
            ['quality', 'quality'],
            //['url','web_site']
        ];

        foreach ($spans as $k => $inf) {
            if ($spanObject = $this->pageObject->find('div.content span[itemprop=' . $inf[0] . ']', 0)) {
                $attr = $inf[1];
                $this->$attr = $spanObject->text();
            }
        }
        return $this;
    }

    public function parseQuality()
    {
        if ($spanObject = $this->pageObject->find('div.content span[itemprope=quality]', 0)) {
            $this->quality = $spanObject->text();
        }
        return $this;
    }

    public function parseWebSite()
    {
        if ($spanObject = $this->pageObject->find('div.content span[itemprop=url] a', 0)) {
            $this->web_site = $spanObject->href;
        }
        return $this;
    }

    public function parseContent()
    {
        if ($spanObject = $this->pageObject->find('div#dle-content div.content', 0)) {
            $this->content = $spanObject->outertext;
        }
        return $this;
    }

    public function parseTitle()
    {
        if ($spanObject = $this->pageObject->find('h1[itemprop=name]', 0)) {
            $this->title = $spanObject->text();
        }
        return $this;
    }

    private function _getTotal($str, $label)
    {
        $obj = HtmlDomParser::str_get_html($str);
        if ($obj && $obj->find('b', 0) && $obj->find('b', 0)->text() == $label) {
            $str = str_replace([$obj->find('b', 0)->outertext, ':', '"'], '', $str);
            $str = trim($str);
            return $str;
        }
        return null;
    }

    public function parseTotal()
    {
        if ($spanObject = $this->pageObject->find('div#dle-content div.content', 0)) {
            $html = str_replace(['<br/>', '<br />'], '<br>', $spanObject->innertext);
            $contentArr = explode('<br>', $html);
            if ($contentArr) {

                foreach ($contentArr as $v) {
                    if ($totalTime = $this->_getTotal($v, 'Total Time')) {
                        $this->total_time = $totalTime;
                    }

                    if ($totalSize = $this->_getTotal($v, 'Total Size')) {
                        $this->total_size = $totalSize;
                    }
                }
            }
        }
        return $this;
    }

    public function parseCategories()
    {
        if ($catObject = $this->pageObject->find('div#dle-content div.content dt.end a')) {
            foreach ($catObject as $aObject) {
                $this->categories[] = $aObject->text();
            }
        }
        return $this;
    }

    private function _handlingDescriptionHtml(){
        $descriptionObject = $this->_getDescriptionObject();
        if ($descriptionObject){
            $replaceObject = $descriptionObject->find('div.quote');
            if ($replaceObject){
                foreach ($replaceObject as $v){
                    if ($v->find('a')){
                        $this->_descriptionHtml = str_replace($v->outertext,'',$this->_descriptionHtml);
                    }
                }
            }
        }
    }

    private function _getDescriptionObject(){
        return HtmlDomParser::str_get_html($this->_getDescriptionHtml());
    }

    private function _getDescriptionHtml (){
        if ($this->_descriptionHtml == null){
            $descObject = $this->pageObject->find('div#dle-content div.content span[itemprop=description]', 0);
            if ($descObject){
                $this->_descriptionHtml = $descObject->innertext;
                $this->_handlingDescriptionHtml();
            } else {
                $this->_descriptionHtml = '';
            }
        }
        return $this->_descriptionHtml;
    }

    public function parseDescription()
    {
        if ($this->_getDescriptionObject() && $descObject = $this->_getDescriptionObject()->find('div.quote', 0)) {
            $this->description = $descObject->innertext;
        }
        return $this;
    }

    public function parseTracklist()
    {
        if ($descObject = $this->_getDescriptionObject()) {
            $this->tracklist = $descObject->innertext;
            if ($replaceBloks = $descObject->find('div')) {
                foreach ($replaceBloks as $v) {
                    $this->tracklist = str_replace($v->outertext, '', $this->tracklist);
                }
            }
        }
    }

    public function parseImgLink()
    {
        if ($imgObject = $this->pageObject->find('div#dle-content div.content div[itemprop=thumbnailUrl] img', 0)) {
            $this->imageLink = $imgObject->src;
        }
        return $this;
    }

    public function parseDownloadLink()
    {
        if ($allLinks = $this->pageObject->find('div#dle-content div.content a')) {
            foreach ($allLinks as $v) {
                if (strstr($v->href, 'https://isra.cloud/')) {
                    $this->download_link_donor = $v->href;
                }
            }
        }
        return $this;
    }


    public function createFilePath()
    {
        $jsonFileName = strtolower(str_replace(['https://', 'http://', '.html'], '', $this->getUrl()));
        $jsonFileName = str_replace(' ', '_', $jsonFileName);
        $jsonFileName = str_replace('/', '-', $jsonFileName);
        $this->filePath = 'parseJsonFiles/albums/' . $jsonFileName . '.json';
    }

    public function loadPage()
    {
        if ($this->loadModel()->title) {
            $this->pageObject = null;
            return $this;
        }
        return parent::loadPage(); // TODO: Change the autogenerated stub

    }

    public function saveArchive()
    {
        $archiveModel = parserAlbumsArchives::getInstance();
        $archiveModel->setDomain($this->download_link_donor);
        $archiveModel->loadPage();
        $this->archivePath = $archiveModel->getFileDownloadPath();
    }

    public function saveArchiveSecondThread()
    {

        $archiveModel = parserAlbumsArchives::getInstance();
        $archiveModel->setDomain($this->download_link_donor);
        $this->archivePath = $archiveModel->getFileDownloadPath();

        if (!is_file($this->archivePath) || filesize($this->archivePath) <= 0) {
            $fileExecPath = Yii::getAlias('@app') . '/tmpScripts/' . uniqid(time()) . '.php';
            $fileExecCodePath = Yii::getAlias('@app') . '/tmpScripts/code/parseAlbumArchives';
            $fileExecCodeContent = file_get_contents($fileExecCodePath);

            $fileExecCodeContent = str_replace('{domain}', $this->download_link_donor, $fileExecCodeContent);

            file_put_contents($fileExecPath, $fileExecCodeContent);
            exec('php ' . $fileExecPath . ' > ' . Yii::getAlias('@app') . '/tmpScripts/logs/output.txt &');
        }
    }

    //public function uploadArchive

    public static function parseAll()
    {
        $jsonFiles = FileHelper::findFiles(\Yii::getAlias('@app') . '/parseJsonFiles/albumLinks');
        $linksInstance = ParserAlbumLinks::getInstance();
        if ($jsonFiles) {
            foreach ($jsonFiles as $v) {
                $linksInstance->setFilePath('parseJsonFiles/albumLinks/' . basename($v))->loadModel();
                if ($linksInstance->links) {
                    foreach ($linksInstance->links as $url) {
                        $albumInstance = static::getInstance(['domain' => $url]);
                        $albumInstance->createFilePath();
                        $albumInstance->loadPage();
                        $albumInstance->parse();
                        $albumInstance->saveArchiveSecondThread();
                        if ($albumInstance->title) {
                            $albumInstance->saveToJson();
                        }
                        //   die();
                    }
                }
            }
        }
    }

    public static function saveArchiveAll()
    {

    }

}