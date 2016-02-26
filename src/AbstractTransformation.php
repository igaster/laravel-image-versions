<?php namespace igaster\imageVersions;

use Intervention\Image\Image;
use Illuminate\Database\Eloquent\Model as Eloquent;

abstract class AbstractTransformation {

	/**
     * Transformatin classes should implement this method
     * Perform here any manipulation to the image. Reveives an Image object.
     * 
     * @param  \Intervention\Image\Image $image
     * @return null
     */	
    // abstract public function apply(Image $image);


	/**
     * This callback is executed before the image is saved. You can override this
     * if you want to prepere the image for saving (eg set file format etc). 
     * 
     * @param  \Intervention\Image\Image $image
     * @return null
     */	
    public function onSaving(Image $image){
        $image->encode('jpg', 75);	
    }

	/**
     * This callback is executed when the image is sucessfuly saved.
     * It receives the decoreted (Version) Eloquent model that encapsulates the Image. 
     * You can perform any post-save actions here (eg update your db etc) 
     * 
     * @param  Version $version
     * @return null
     */	
    public function onSaved(Version $version){
		// $version->relativePath()	// new image relative path , alias to $version->url()
		// $version->absolutePath() // new image relative path , alias to $version->url()
		// $version->id 			// Access your oroginal Eloquent model's attributes/methods
    }

}

