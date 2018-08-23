<?php
/**
 * Created by PhpStorm.
 * User: programmer_5
 * Date: 03.08.2018
 * Time: 11:18.
 */

namespace app\models;

use Yii;
use yii\helpers\FileHelper;
use Sunra\PhpSimple\HtmlDomParser;

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

    public $download_link;

    public $web_site;

    public $genre;


    public $archivePath;

    public function rules()
    {
        return [
            [
                [
                    'archivePath', 'domain', 'categories', 'title', 'artist', 'imageLink', 'year_of_release', 'tracklist', 'description', 'label', 'quality', 'total_time', 'total_size', 'download_link_donor', 'download_link', 'web_site', 'genre', 'content'
                ], 'safe'],
        ];
    }

    public function fields()
    {
        $fields = parent::fields();
        $fields['donor_link'] = 'domain';
        unset($fields['url'], $fields['filePath'], $fields['filePath'], $fields['pageObject'], $fields['logsPath']);

        return $fields;
    }

    public function parseSpans()
    {
        if (!$this->pageObject) {
            return $this;
        }
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


    public function parseTitle()
    {
        if ($spanObject = $this->pageObject->find('h1[itemprop=name]', 0)) {
            $this->title = $spanObject->text();

            $this->title = str_replace('&#039;','\'',$this->title);
            $this->title = addslashes($this->title);
           // var_dump($this->title); die();
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
        $this->categories=[];
        if ($catObject = $this->pageObject->find('div#dle-content div.content dt.end a')) {
            foreach ($catObject as $aObject) {
                $this->categories[] = str_replace(['&amp;', '  '], ['', ' '], $aObject->text());
            }

        }
        return $this;
    }

    private function _replaceHtml($obj){
        if ($obj) {
            foreach ($obj as $item) {
                $this->description = str_replace($item->outertext, '', $this->description);
            }
        }
    }

    private function _replaceDubleBR($brStyle='<br>'){
        $array = explode($brStyle.$brStyle,$this->description);
        foreach ($array as $k=>$v){
            if (!trim($v)){
                unset($array[$k]);
            }
        }
        $this->description = implode($brStyle.$brStyle,$array);
    }

    private function _handlingDescriptionHtml()
    {
        if ($this->description && $descriptionObject = HtmlDomParser::str_get_html($this->description)){
            $replaceSelectors = [
                'img','comment','a','div[style="text-align:center;"]'
            ];
            foreach ($replaceSelectors as $selector){
                if ($descriptionObject) {
                    $this->_replaceHtml($descriptionObject->find($selector));
                }
                $descriptionObject = HtmlDomParser::str_get_html($this->description);
            }
            $this->description = strip_tags($this->description, '<br><br /><br/>');
            $brStyles = ['<br>','<br />','<br/>'];
            foreach ($brStyles as $brStyle){
                $this->_replaceDubleBR($brStyle);
            }
            $this->description = trim($this->description);
        }
    }


    public function parseDescription()
    {
        $descObject = $this->pageObject->find('div#dle-content div.content span[itemprop=description]', 0);
        if ($descObject) {
            $this->description = $descObject->innertext;
            $this -> _handlingDescriptionHtml();
        }
        return $this;
    }

    public function parseTracklist()
    {
//        if ($descObject = $this->_getDescriptionObject()) {
//            $this->tracklist = $descObject->innertext;
//            if ($replaceBloks = $descObject->find('div')) {
//                foreach ($replaceBloks as $v) {
//                    $this->tracklist = str_replace($v->outertext, '', $this->tracklist);
//                }
//            }
//        }
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
        $this->filePath = '@app/music_files/json/albums/' . $jsonFileName . '.json';
    }

//    public function loadPage()
//    {
//        if ($this->loadModel()->title) {
//            $this->pageObject = null;
//
//            return $this;
//        }
//
//        return parent::loadPage(); // TODO: Change the autogenerated stub
//    }

    public function downloadFileExist()
    {
        return (new UploadAlbumArchive())->fileExist($this->download_link);
    }

    public function saveArchive()
    {
        if (!$this->downloadFileExist() && !is_file(Yii::getAlias($this->getArchivePath()))) {
            $archiveModel = ParserAlbumsArchives::getInstance(['domain' => $this->download_link_donor, 'archivePath' => $this->getArchivePath()]);
            $archiveModel->loadPage();

            try {
                $archiveHandling = ArchiveHandling::getInstance(['filePath' => $this->getArchivePath()]);
                $archiveHandling->unarchive();
                $archiveHandling->handlingTmpDir();
                $archiveHandling->archive();
                $this->archivePath = $archiveHandling->getNewFilePath();
            } catch (\Exception $e) {

            }

        }
        return $this;
    }

    public function uploadArchive($delete = false)
    {
        if (!$this->downloadFileExist()) {
            $uploadModel = new UploadAlbumArchive();
            $uploadModel->filePath = $this->archivePath;
            $this->download_link = $uploadModel->upload();
            if ($delete) {
                $uploadModel->deleteLocalArchive();
            }
        }
        return $this;
    }

    public function getArchivePath()
    {
        return '@app/music_files/archives/' . str_replace('.html', '', basename($this->download_link_donor));
    }

    public static function pAlbum($url)
    {
        $albumInstance = static::getInstance(['domain' => $url]);
        $albumInstance->createFilePath();
        $albumInstance->loadPage();
        $albumInstance->parse();

        if ($albumInstance->title) {
            $albumInstance->saveToJson();
            SecondThread::execStatic(['route' => 'parser/albums-archives', 'params' => [$albumInstance->filePath]], 2);
        }
        return $albumInstance;
    }

    public function saveToDb()
    {
        $instance = Albums::getInstanceParser($this->toArray());
        $instance->save();
        $instance->saveCategoryAlbums();
        $instance->saveTypeAlbums();
    }

    public static function parseAll()
    {
        $jsonFiles = FileHelper::findFiles(Yii::getAlias('@app/music_files/json/album_links'));
        $linksInstance = ParserAlbumLinks::getInstance();
        if ($jsonFiles) {
            foreach ($jsonFiles as $v) {
                $linksInstance->setFilePath($v)->loadModel();
                if ($linksInstance->links) {
                    foreach ($linksInstance->links as $url) {
                        $instance = static::pAlbum($url);
                        //  $instance->saveToDb();
                    }
                }
            }
        }
    }

    public static function partParsing($pagesModel = [])
    {
       // var_dump($pagesModel); die();
        if ($pagesModel) {
            foreach ($pagesModel as $v) {
                if ($v->links) {
                    foreach ($v->links as $url) {
                        $instance = static::pAlbum($url);
                        // $instance->saveToDb();
                    }
                }
            }
        }
    }

}
//13:07