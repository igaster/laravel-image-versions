<?php namespace igaster\imageVersions\Transformations;

// Helper Transformation: Scales + Crops an image to fit in a Rectangle
// Overide this and define $widht and $height

use Imagick;

class ScaleAndCrop extends \igaster\imageVersions\AbstractTransformation{

    // Redifine dimensions in child class
    public $width = 100;
    public $height = 100;

    // Scale Image to Fill Box - Crop outside of Box
    public function scaleAndCrop(Imagick $image, $width, $height){

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

    public function apply(\Imagick $image){
       $this->scaleAndCrop($image, $this->width, $this->height);
    }

}

