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

    public $categories = [];

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
            [['created_at', 'updated_at'], 'integer'],
            [
                [
                    'donor_link', 'title', 'artist', 'image', 'year_of_release', 'tracklist', 'description', 'label', 'genre',
                    'quality', 'total_time', 'total_size', 'download_link', 'big_image', 'web_site'
                ],
                'string'
            ],
        ];
    }

    public function setCategories($categories)
    {
        if ($categories && is_array($categories)) {
            $this->categories = [];
            foreach ($categories as $level => $categoryName) {
                $parent = isset($categoryModel) && $categoryModel ? $categoryModel->id : null;
                $categoryModel = Categories::getInstanceWithSave($categoryName, $parent, $level);
                $this->categories[] = $categoryModel->id;
            }
        }
        return $this;
    }

    public function saveCategoryAlbums()
    {
        if ($this->categories && $this->id) {
            CategoryAlbums::deleteAll(['album_id' => $this->id]);
            foreach ($this->categories as $categoryID) {
                $categoryAlbumModel = new CategoryAlbums();
                $categoryAlbumModel->attributes = [
                    'album_id' => $this->id,
                    'category_id' => $categoryID
                ];
                $categoryAlbumModel->save();
            }
        }
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