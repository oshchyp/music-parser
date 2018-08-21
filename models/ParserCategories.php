<?php
/**
 * Created by PhpStorm.
 * User: programmer_5
 * Date: 21.08.2018
 * Time: 10:51
 */

namespace app\models;


class ParserCategories extends Parser
{

    public $filePath = '@app/music_files/json/categories.json';

    public $categories = [];

    public $categoriesConvert = [];

    public $categoriesConvertSecond = [];

    public function fields()
    {
        return ['categories','categoriesConvert','categoriesConvertSecond'];
    }

    public function parseCategories()
    {
        $menuObject = $this->findDom('ul#menu li');

        if ($menuObject) {
            foreach ($menuObject as $object) {
                 $this->categories[] = [
                    'name' => trim($object->text()),
                    'subLevel' => $object->getAttribute('class') ? (int)str_replace('sub-','',$object->getAttribute('class')) : 0
                ];
           }
        }
        return $this;
    }

    public function convertCategories(){
        if ($categories = $this->categories){
            foreach ($categories as $k => $v){
                $parentLevel = $v['subLevel']-1;
                if ($v['subLevel'] > 0 && isset($lastParams) && isset($lastParams[$parentLevel])){
                    $categories[$k]['parent'] = $lastParams[$parentLevel];
                }
                $lastParams[$v['subLevel']] = $k;
            }
            $this->categoriesConvert = $categories;
        }

        return $this;
    }


    public function saveToDB(){
        if ($categories = $this->categoriesConvert){
            $conformityIds=[];
            foreach ($categories as $k=>$v){
                 $model = static::getCategoryInstance($v['name']);
                 $model->level = $v['subLevel'];
                 if (isset($v['parent']) && isset($conformityIds[$v['parent']])){
                     $model->father_id = $conformityIds[$v['parent']];
                 }
                 if ($model->save()){
                     $conformityIds[$k] = $model->id;
                 }

            }
        }
        return $this;
    }

    public static function getCategoryInstance($name){
        $model = Categories::find()->where(['name'=>$name])->one();
        return $model ? $model : new Categories();
    }


//    public function convertCategoriesSecond(){
//        $this->categoriesConvertSecond = $this->recursiveConvertCategories($this->categoriesConvert);
//        return $this;
//    }
//
//    public function recursiveConvertCategories($categories=[],$parent = 0){
//        $result = [];
//        if ($categories) {
//            foreach ($categories as $k=>$v){
//                if (isset($v['parent']) && $v['parent'] == $parent){
//                    unset($categories[$k]);
//                    $v['subs'] = $this->recursiveConvertCategories($categories,$k);
//                    $result[] = $v;
//                }
//            }
//        }
//        return $result;
//    }


}