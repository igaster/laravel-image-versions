<?php namespace igaster\imageVersions;

use igaster\EloquentDecorator\EloquentDecoratorTrait;

use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Routing\UrlRoutable;
use Illuminate\Contracts\Queue\QueueableEntity;
use ArrayAccess;
use JsonSerializable;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Version implements ArrayAccess, Arrayable, Jsonable, JsonSerializable, QueueableEntity, UrlRoutable {
    use EloquentDecoratorTrait;

    public $transformation = null;

    public static function apply(Eloquent $image, $transformation){
      $version = static::wrap($image);
      $version->transformation = $transformation;

      if(!file_exists($version->absolutePath()))
        $version->buildNewImage();

      return $version;
    }

    public function versionName(){
      $function = new \ReflectionClass($this->transformation);
      return $function->getShortName();
    }

    public function relativePath(){
      $pathinfo = pathinfo($this->object->relativePath());
      $filename = $pathinfo['basename'];
      $path = $pathinfo['dirname'];
      if (empty($path) || $path=='.')
        $path = $this->versionName();
      else  
        $path .= '/'.$this->versionName();

      return "$path/$filename";
    }

    public function absolutePath(){
      return public_path($this->relativePath());
    }

  	public function buildNewImage(Imagick $image = null){  

  		if(empty($image)){
        $image =new \Imagick();
        $image->readImage($this->object->absolutePath());
      }

  		$targetFile = $this->absolutePath();

      $dirName = dirname($targetFile);
      if (!\File::isDirectory($dirName))
          \File::makeDirectory($dirName, 0777, true);

  		$class_name = $this->transformation;
          $class_name::applyTransformations($image);

      $image->setImageCompressionQuality(66);
      $image->setImageCompression(\Imagick::COMPRESSION_JPEG);
      $image->stripImage();

      $image->writeImage($targetFile);    
  	}


}