<?php namespace igaster\imageVersions\Transformations;

// Helper Transformation: Scales + Distorts an image to fit in a Rectangle
// Overide this and define $widht and $height

use Intervention\Image\Image;

class Crop extends \igaster\imageVersions\AbstractTransformation{

    // Redifine dimensions in child class
    public $width = 100;
    public $height = 100;

    // Perform the manipulation
    public function apply(Image $image){
        $image->crop($this->width, $this->height);
    }

}

