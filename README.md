[![Laravel](https://img.shields.io/badge/Laravel-5.x-orange.svg)](http://laravel.com)
[![License](http://img.shields.io/badge/license-MIT-brightgreen.svg)](https://tldrlegal.com/license/mit-license)
[![Downloads](https://img.shields.io/packagist/dt/igaster/laravel-image-versions.svg)](https://packagist.org/packages/igaster/laravel-image-versions)
[![Build Status](https://img.shields.io/travis/igaster/laravel-image-versions.svg)](https://travis-ci.org/igaster/laravel-image-versions)
[![Codecov](https://img.shields.io/codecov/c/github/igaster/laravel-image-versions.svg)](https://codecov.io/github/igaster/laravel-image-versions)

## How it works

Enchace your models to produce different versions of any image (eg for thumbnail/watermark etc). Produced images are saved in separate subfolders for future requests. You only have to define some operations on the Image. File management and caching are handled for you.

## Installation

install with

    composer require "igaster/laravel-image-versions"

## How to use

This package decorates any Eloquent Model that holds a representation of an Image. To implement follow these steps:

1. Setup your model:

Your model should use the `ImageVersionsTrait`

    use \igaster\imageVersions\ImageVersionsTrait;

The only requirement for your model is to define the `relativePath()` method which will return the path to the file (relative to public folder). For example if you place your images in a `Photos` folder and store the naked filename in the `filename` attribute then your implementatin could be:

    public function relativePath(){
        return 'Photos\'.$this->filename;  // example output: "Photos\image1.jpg" 
    }                                      // No beggining or trailing slashes

2. Create your Transformation classes:

You can create any number of versions of a single image with the use of Imagick. To define a version you have to create a  Transformation class that extends the `AbstractTransformation` and implement the `apply()` method. You will receive an Imagick object, where you can perform any image-operations.

A short example of a trasformation class:

    class v200x200 extends \igaster\imageVersions\AbstractTransformation{

        public function apply(\Imagick $image){
            // Perform any operations on the $image object
            $image->thumbnailImage(200, 200);
        }
    
    }

3. Request an image version

Very simple! Call the `version()` method on your Eloquent model. We will use a `Photo` class as an example Eloquent model that stores an image filename:

	$photo = Photo::find(1)->version(v200x200::class); // get the `v200x200' version of your Photo model

Here's what is going to happen:

- Your orinal image will be loaded from the disk
- It will be fed to the `v200x200` transformation class (The `apply()` method will be called)
- The new image will be saved with the same name in a subfolder with the name of your transformation (`v200x200` here)
- The next time you will request the same version of the same image you will receive the saved version! Build on request + cache!

On the `$photo` object you received, you can retreive information about the new version of the image:

    $photo->url() // the url to your image (eg /Photos/v200x200/filename.jpg)
    $photo->relativePath() // path relative to public  (eg Photos/v200x200/filename.jpg)
    $photo->absolutePath() // absolute path. You can perform file operations on this

## Decorator Pattern: You still have your model

Morever the `$photo` object is a **decorator** that wraps your original Photo model. This means that you can perform ANY operation you could perform on your original model.

	// Decorator pattern applied.
	// You can call any method of your Photo Eloquent model. Example:
	
	$photo->id;							// Get / Set an Eloquent attribute
	$photo->update(['key' => 'value']);	// Call any Eloquent's methods
	$photo->myMehod();					// Call methods that you have defined in the Photo class

More information about the decorator used in [igaster/eloquent-decorator](https://github.com/igaster/eloquent-decorator)