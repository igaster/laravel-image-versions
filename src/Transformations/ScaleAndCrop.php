<?php namespace igaster\imageVersions\Transformations;

// Helper Transformation: Scales + Crops an image to fit in a Rectangle
// Overide this and define $widht and $height

use Intervention\Image\Image;

class ScaleAndCrop extends \igaster\imageVersions\AbstractTransformation{

    // Redifine dimensions in child class
    public $width = 100;
    public $height = 100;

    public function apply(Image $image){
        $image->fit($this->width, $this->height, function ($constraint) {
            $constraint->upsize();
        });
    }

}

