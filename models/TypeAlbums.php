<?php
/**
 * Created by PhpStorm.
 * User: programmer_5
 * Date: 16.08.2018
 * Time: 16:25
 */

namespace app\models;


use yii\db\ActiveRecord;

class TypeAlbums extends ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'type_albums';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['album_id', 'type_id'], 'integer'],

        ];
    }

}