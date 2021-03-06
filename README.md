[![Laravel](https://img.shields.io/badge/Laravel-5.x-orange.svg)](http://laravel.com)
[![License](http://img.shields.io/badge/license-MIT-brightgreen.svg)](https://tldrlegal.com/license/mit-license)
[![Build Status](https://img.shields.io/travis/igaster/laravel-image-versions.svg)](https://travis-ci.org/igaster/laravel-image-versions)

## How it works

Enchace your models to produce different versions of any image (eg for thumbnail/watermark etc). Produced images are saved in separate subfolders for future requests. You only have to define some operations on the Image. File management and caching are handled for you. You can use any local or remote filesystem you need!

## Installation

This package depends on the [Intervention](http://image.intervention.io) package to manipulate images. 

First you should install `Intervention`: [Read the instructions](http://image.intervention.io). Don't forget to publish it's configuration file, we will place some options there!

Next you can install this package with:

    composer require "igaster/laravel-image-versions"

## How to use

This package decorates any Eloquent Model that holds a representation of an Image. To implement follow these steps:

#### 1. Setup your model:

Your model should use the `ImageVersionsTrait`. For example:

```php
class Photo extends Eloquent {
   use \igaster\imageVersions\ImageVersionsTrait;
   //...
}
```

The only requirement for your model is to define the `relativePath()` method which will return the path to the file (relative to public folder). For example if you place your images in a `Photos` folder and store the naked filename in the `filename` attribute then your implementatin could be:

```php
class Photo extends Eloquent {

    public function relativePath(){        // No starting or trailing slashes
        return 'Photos\'.$this->filename;  // example output: "Photos\image1.jpg" 
    }

}

```

PS: If you are using Amazon S3 (or any other flysystem disk) then this method should return the path to your file from your disk's root.

#### 2. Create your Transformation classes:

You can create any number of versions of a single image. To define a version you have to create a  Transformation class that extends the `AbstractTransformation` and implement the `apply()` method. You will receive an `Intervention\Image\Image` object, where you can perform any operations.

A short example of a trasformation class:

```php
use Intervention\Image\Image;

class v200x200 extends \igaster\imageVersions\AbstractTransformation{

    public function apply(Image $image){
        // Perform here any operations on the $image object, eg:
        $image->crop(200, 200);
    }

}
```

The `Intervention` package provides a rich API to edit your images. Refer to their documentation for a [list of all available methods](http://image.intervention.io)

#### 3. Request an image version

Very simple! Call the `version()` method on your Eloquent model.  (For the following example suppose that `Photo` class is an Eloquent model that stores an image's filename)

```php
$photo = Photo::find(1)                      // Photo can be any Eloquent model in your application
$thumb = $photo->version(v200x200::class);   // Get the `v200x200' version of your $photo
```

Here's what is going to happen:

- Your orinal image will be loaded from the disk
- It will be fed to the `v200x200` transformation class (The `apply()` method will be called)
- The new image will be saved with the same name in a subfolder with the name of your transformation (`v200x200` here)
- The next time you will request the same version of the same image you will receive the saved version! Build on request + cache!

On the `$thumb` object you received, you can retreive information about the new version of the image:

```php
$thumb->url() // the url to your image (eg /Photos/v200x200/filename.jpg)
$thumb->relativePath() // path relative to public  (eg Photos/v200x200/filename.jpg)
$thumb->absolutePath() // absolute path (valid only on local filesystem). You can perform file operations on it
```

If you want to force rebuilding the new image even if it has been cached before you can call `rebuildVersion()` instead of `version()` 

## Passing Parameters

You may pass any number of parameters when you request a version of an Image:

```php
$cropped = $photo->version(vCrop::class, 200, 300);
```

You will receive these values in the `apply()` method of your Transformation class as additional parameters:

```php
public function apply(Image $image, $width, $height){
    $image->cropImage($width, $height, 0, 0);
}
```

Please note that the the Transformation is executed only if the new image does not exist. If it has been called in the past, then the stored image will be returned instead of creating a new one. s

## Using Flysystem disks (eg Amazon S3)

You can swap your local filesystem with any remote file system such as the Amazon S3.

- First define your filesystem disk in your 'filesystems.php' configuration file
- Now copy these options in the 'image.php' configuration file (it was provided by Intervention package):

```php
'versions' => [

    /*
    |  Set the disk that you want to use. Can be any disk defined in 'filesystems.php' 
    |  Leave null to default to your public folder.
    */

    'disk' => 's3',

    /*
    |  You can set here the endpoind of you filesystem. This will be used to get a url() for your
    |  images. Leave null if you are saving localy
    */

    'root_url' => 'my_bucket.s3-website-eu-west-1.amazonaws.com',
]
```

## Using Aliases

You can define the default namespace of your Transformation classes in `image.php` configuration file:

```php
'versions' => [

    /*
    |  This is the namespace of your Transformation classes. If you define this then
    |  you can either use the full transformation's class name, or the short class name
    |  when you are calling the version() method. Default: null
    */

    'namespace' => Namespace\Of\Transformations\Classes,
]

```


Now you have the option to use the Transformation class shortname as an alias instead of the full nampespaced classname. eg:

```php
$photo = Photo::find(1);

// The following are equivalent:
$thumb = $photo->version(Namespace\Of\Transformations\Classes\v200x200::class);
$thumb = $photo->version('v200x200');
```

This is quite usefull when you are using image versions inside your Blade files.

## Saving lifecycle

Two callbacks will be fired from your Transformation class before and after saving the new image. Your can implement these methods if in your classes if you need extra functionality:

```php
class v200x200 extends \igaster\imageVersions\AbstractTransformation{

	/**
     * This callback is executed before the image is saved. You can override this
     * if you want to prepere the image for saving (eg set file format etc). 
     * 
     * @param  \Intervention\Image\Image $image
     * @return null
     */	
    public function onSaving(\Intervention\Image\Image $image){
        $image->encode('jpg', 75);  
    }

	/**
     * This callback is executed when the image is sucessfuly saved.
     * It receives the decorated (Version) Eloquent model that encapsulates the Image. 
     * You can perform any post-save actions here (eg update your db / fire events etc) 
     * 
     * @param  Version $version (Your original Eloquent model decorated)
     * @return null
     */	
    public function onSaved(Version $image){
    	/* examples:
			$image->id;              // Access your original Eloquent model's attributes/methods
            $image->url();           // created image's url
            $image->relativePath();  // created image's relative path
            $image->absolutePath();  // created image's absolute (valid only on local filesystem)
		*/
    }

}
```

Take a look at the [AbstractTransformation](https://github.com/igaster/laravel-image-versions/blob/master/src/AbstractTransformation.php) to see how saving an image is handled by default. Note that you should not write the image to a file by yourself (`writeImage('...')`), since this is already handled for you.

## Custom Callbacks

You can define any number of callback functions that will be executed BEFORE your Transformation is applied. To set your callbacks call `beforeTransformation()` from your Eloquent model, before calling the `version()` method:

```php
$thumb = $photo->beforeTransformation(function(\Intervention\Image\Image $image){
    $image->crop(200, 200, 0, 0);
})->version(vGrayscale::class);
``` 
Usefull if you want to peform some preprocessing. You can chain any number of `beforeTransformation()` calls. 

Additionaly you can pass your own parameters to your callbacks: 

```php
$thumb = $photo->beforeTransformation(function(\Intervention\Image\Image $image, $width, $height){
    $image->crop($width, $height, 0, 0);
}, 200, 200)->version(vGrayscale::class);  // Now the crop size is not hardcoded!
``` 

## Decorator Pattern: You still have your models!

Morever the return value of the `version()` funtion (instance of [Version](https://github.com/igaster/laravel-image-versions/blob/master/src/Version.php)) is **decorator** that wraps your original Photo model. This means that you can perform ANY operation you could perform on your original model.

```php
// Decorator pattern applied.
// You can call any method of your Photo Eloquent model. Example:

$thumb->user_id;					// Get, Set an Eloquent attribute
$thumb->update(['key' => 'value']);	// Call any Eloquent's methods
$thumb->myMehod();					// Call methods that you have defined in the Photo class
$thumb->object;                     // Instance of the original Photo object 
```

You can find more information about the decorator used at [igaster/eloquent-decorator](https://github.com/igaster/eloquent-decorator)