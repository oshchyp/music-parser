<?php


include_once '../index.php';

// dump(count(scandir('/Users/programmer_5/Sites/parser-music/parseJsonFiles/albums')));
// dump(count(scandir('/Users/programmer_5/Sites/parser-music/archives/download')));

$model = new \app\models\UploadAlbumArchive();

$model->filePath = '/var/www/parser_music/archives/download/Francis_Instrumental___40_2018__41__FLAC.rar';

$model->upload();
