<?php namespace igaster\imageVersions\Transformations;

// Helper Transformation: Scales + Distorts an image to fit in a Rectangle
// Overide this and define $widht and $height

use Imagick;

class Thumbnail extends \igaster\imageVersions\AbstractTransformation{

    // Redifine dimensions in child class
    public $width = 100;
    public $height = 100;

    // Perform the manipulation
    public function apply(\Imagick $image){
        $image->thumbnailImage($this->width, $this->height);
    }

}

