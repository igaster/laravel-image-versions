<?php namespace igaster\imageVersions\Transformations;

// Helper Transformation: Scales + Distorts an image to fit in a Rectangle
// Overide this and define $widht and $height

use Intervention\Image\Image;

class Thumbnail extends \igaster\imageVersions\AbstractTransformation{

    // Redifine dimensions in child class
    public $width  = 100;
    public $height = 100;

    // Perform the manipulation
    public function apply(Image $image){
        $iHeight = $image->height();
        $iWidth = $image->width();
        
        if ($iHeight == $height && $iWidth == $width) return $image;

        $iRatio = $iWidth/$iHeight;
        $Ratio = $width/$height;

        if($iRatio > $Ratio) {
            $image->resize($width, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }
        else {
            $image->resize(null, $height, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }
        return $image;
    }

}

