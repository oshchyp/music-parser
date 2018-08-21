<?php
/**
 * Created by PhpStorm.
 * User: programmer_5
 * Date: 16.08.2018
 * Time: 12:40
 */

namespace app\models;


use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

class Albums extends ActiveRecord
{

    public $types = [];

    public $imgDir = '@app/../music.com/public/images';

    public $imgPublicDir = '/images';

    public $categories;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'albums';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['categories'], 'safe'],
            [['donor_link'], 'unique'],
          //  [['created_at', 'updated_at'], 'integer'],
            [
                [
                    'donor_link', 'imageLink', 'title', 'artist', 'image', 'year_of_release', 'tracklist', 'description', 'label', 'genre',
                    'quality', 'total_time', 'total_size', 'download_link', 'big_image', 'web_site'
                ],
                'string'
            ],
        ];
    }

    public function getTypeRules()
    {
        return [
            '2018' => 1,
            'Mp3' => 2,
            'CD-Rip' => 3,
            'HD & Vinyl' => 4,
            'FLAC / APE' => 5,
            'iTunes' => 6,
            'Discography' => 7
        ];
    }

     public function beforeValidate()
     {
        if (!$this->created_at){
            $this->created_at = time();
        }
        $this->updated_at = time();
        return true;
     }

    public function setImageLink($link)
    {
        $this->imageLink = $link;
        if ($link && is_string($link)) {
            $imgName = basename($link);
            $filePath = \Yii::getAlias($this->imgDir) . '/' .$imgName;
            $imgPublicPath = \Yii::getAlias($this->imgPublicDir).'/'.$imgName;
            file_put_contents($filePath,file_get_contents($link));
            if (is_file($filePath)){
                $this->image = $this->big_image = $imgPublicPath;
            }
        }
        return $this;
    }

//    public function setCategories($categories)
//    {
//        if ($categories && is_array($categories)) {
//            $this->categories = [];
//            foreach ($categories as $level => $categoryName) {
//
//                if ($categoryModel = Categories::find()->where(['name' => $categoryName])->one()){
//                    $this->categories[] = $categoryModel;
//                }
//
//            }
//        }
//        return $this;
//    }

    public function saveCategoryAlbums()
    {
        if ($this->categories && $this->id) {
            CategoryAlbums::deleteAll(['album_id' => $this->id]);
            foreach ($this->categories as $categoryName) {
                if ($categoryObject = Categories::find()->where(['name' => $categoryName])->one()){
                    $categoryAlbumModel = new CategoryAlbums();
                    $categoryAlbumModel->attributes = [
                        'album_id' => $this->id,
                        'category_id' => $categoryObject->id
                    ];
                    $categoryAlbumModel->save();
                }
            }
        }
        return $this;
    }

    public function saveTypeAlbums()
    {
      //  var_dump($this->categories); die();
        if ($this->categories && $this->id) {
            TypeAlbums::deleteAll(['album_id' => $this->id]);
            foreach ($this->categories as $categoryName) {
                if ($typeID = ArrayHelper::getValue($this->getTypeRules(), $categoryName)) {
                    $typeAlbums = new TypeAlbums();
                    $typeAlbums->attributes = [
                        'album_id' => $this->id,
                        'type_id' => $typeID
                    ];
                    $typeAlbums->save();
                }
            }
        }
        return $this;
    }

    public static function findByDonorLink($donorLink)
    {
        return static::find()->where(['donor_link' => $donorLink])->one();
    }

    public static function getInstanceParser($attributes)
    {
        $instance = static::findByDonorLink(ArrayHelper::getValue($attributes, 'donor_link'));
        if (!$instance) {
            $instance = new static();
        }
        $instance->attributes = $attributes;
        return $instance;
    }


}
///var/www/www-root/data/www/music-parser/ohm_music.sql