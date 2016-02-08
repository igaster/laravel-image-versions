<?php namespace igaster\imageVersions\Tests\App\Transformations;

use igaster\imageVersions\Transformations\Thumbnail;

class v200x200 extends Thumbnail{

    public static $width = 200;
    public static $height = 200;

    // public static function applyTransformations(\Imagick $Image){
    //     $Image = ImageUtils::scaleCrop($Image, new cBox(200,200));
    // }
}