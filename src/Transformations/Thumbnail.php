<?php namespace igaster\imageVersions\Transformations;

// Helper Transformatin. Overide this and define $widht and $height
// Scales + Distorts an image to fit in a Rectangle

use Imagick;

class Thumbnail implements \igaster\imageVersions\TransformationInterface{

    // Redifine dimensions in child class
    public static $width = 100;
    public static $height = 100;

    // Perform the manipulation
    public static function applyTransformations(\Imagick $image){
        $image->thumbnailImage(static::$width, static::$height);
    }

}

