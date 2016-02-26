<?php namespace igaster\imageVersions\Tests\App\Transformations;

use Intervention\Image\Image;

class vParameters extends \igaster\imageVersions\AbstractTransformation{

    public function apply(Image $image, $p1=null, $p2=null, $p3=99){
    	if(!empty($p1))
    		echo "$p1,$p2,$p3";
    }

}