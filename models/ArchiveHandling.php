<?php
/**
 * Created by PhpStorm.
 * User: programmer_5
 * Date: 21.08.2018
 * Time: 13:22
 */

namespace app\models;


class ArchiveHandling extends model
{

    public $filePath;

    public $tmpDir = '@app/music_files/archive_handling/tmp';

    public $tmpFilePath;

    public static function getInstance($params = [])
    {
        $instance = new static();
        if ($params) {
            foreach ($params as $attr => $value) {
                $instance->$attr = $value;
            }
        }

        return $instance;
    }

    public static function getOrCreateDir($path)
    {
        if (!is_dir(pathinfo($path)['dirname'])) {
            FileHelper::createDirectory(pathinfo($path)['dirname']);
        }

        return $path;
    }

    public function unarchive(){
        var_dump(pathinfo($this->filePath)); die();
    }

}