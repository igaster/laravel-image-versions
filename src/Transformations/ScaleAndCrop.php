<?php namespace igaster\imageVersions\Transformations;

// Helper Transformatin. Overide this and define $widht and $height
// Scales + Crops an image to fit in a Rectangle

use Imagick;

class ScaleAndCrop implements \igaster\imageVersions\TransformationInterface{

    // Redifine dimensions in child class
    public static $width = 100;
    public static $height = 100;

    // Scale Image to Fill Box - Crop outside of Box
    public static function scaleAndCrop(Imagick $image, $width, $height){

        $iHeight = $image->getImageHeight();
        $iWidth = $image->getImageWidth();

        if ($iHeight == $height && $iWidth == $width) return $image;

        $iRatio = $iWidth/$iHeight;
        $Ratio = $width/$height;

        if($iRatio > $Ratio) {
            $fWidth = $iWidth * ($height/$iHeight);
            $image->scaleImage(0,$height);
            $image->cropImage($width,$height,($fWidth-$width)/2,0);
        }
        else {
            $fHeight = $iHeight * ($width/$iWidth);
            $image->scaleImage($width,0);
            $image->cropImage($width,$height,0,($fHeight-$height)/2);
        }
        return $image;
    }

    // Perform the manipulation
    public static function applyTransformations(\Imagick $image){
        $image = self::scaleAndCrop($image, static::$width, static::$height);
    }

}

