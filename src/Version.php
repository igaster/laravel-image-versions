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

    public $transformationClass = null;

    public static function apply(Eloquent $image, $transformationClass){
      $version = static::wrap($image);
      $version->transformationClass = $transformationClass;

      if(!file_exists($version->absolutePath()))
        $version->buildNewImage();

      return $version;
    }

    public function className(){
      $className = $this->transformationClass;

      if (class_exists($className))
        return $className;

      if(isset($this->object->transformationNamespace) && !empty($namespace = $this->object->transformationNamespace)){
        $className =  sprintf("%s\%s",$namespace, $className);
      }

      if (class_exists($className))
        return $className;

      throw new \Exception("Image Transformation: '{$className}' does not exists", 1);
    }

    public function versionName(){
      $function = new \ReflectionClass($this->className());
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

    public function url(){
      return $this->relativePath();
    }

    public function buildNewImage(){  

      $sourceFile = $this->object->absolutePath();
      $targetFile = $this->absolutePath();
      $targetPath = dirname($targetFile);

      $image =new \Imagick();
      $image->readImage($sourceFile);

      $transformationClass = $this->className();
      $transformation = new $transformationClass();

      $transformation->apply($image);
      $transformation->onSaving($image);
      $transformation->onSaved($this);

      if (!\File::isDirectory($targetPath))
          \File::makeDirectory($targetPath, 0777, true);
      $image->writeImage($targetFile);    
  	}


}