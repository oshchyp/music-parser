<?php
/**
 * Created by PhpStorm.
 * User: programmer_5
 * Date: 16.08.2018
 * Time: 13:54
 */

namespace app\models;


use yii\db\ActiveRecord;

class CategoryAlbums extends ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'categories';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['father_id'],'safe'],
            //   [['donor_link'], 'unique'],
            [['level', 'created_at', 'updated_at'], 'integer'],
            [['name'], 'string'],
        ];
    }

}