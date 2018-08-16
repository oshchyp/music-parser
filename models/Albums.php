<?php
/**
 * Created by PhpStorm.
 * User: programmer_5
 * Date: 16.08.2018
 * Time: 12:40
 */

namespace app\models;


use yii\base\Model;

class Albums extends Model
{

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
//            [[],'unique'],
//            [[], 'required'],
            [['created_at','updated_at'], 'integer'],
            [['donor_link','title','artist','image','year_of_release','tracklist','description','label','genre','quality','total_time','total_size','download_link','big_image','web_site'], 'string'],
        ];
    }

}