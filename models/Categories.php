<?php
/**
 * Created by PhpStorm.
 * User: programmer_5
 * Date: 16.08.2018
 * Time: 13:17
 */

namespace app\models;


use yii\db\ActiveRecord;

class Categories extends ActiveRecord
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

    public static  function getInstanceWithSave($name,$parent=null,$level=0)
    {
        $query = static::find()->where(['name'=>$name]);
        if ($parent){
            $query->andWhere(['father_id' => $parent]);
        }
        $instance = $query->one();
        if (!$instance){
            $instance = new static(['name'=>$name,'father_id' => $parent,'level' => $level]);
            $instance->save();
        }
      //  dump($instance->getErrors());
        return $instance;
    }

}