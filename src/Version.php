<?php namespace igaster\imageVersions;

use igaster\EloquentDecorator\EloquentDecoratorTrait;

use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Routing\UrlRoutable;
use Illuminate\Contracts\Queue\QueueableEntity;
use ArrayAccess;
use JsonSerializable;
use File;
use Storage;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Version implements ArrayAccess, Arrayable, Jsonable, JsonSerializable, QueueableEntity, UrlRoutable {
    use EloquentDecoratorTrait;

    public $transformationClass = null;
    public $callbacks = [];

    public static function decorate(Eloquent $image, $transformationClass, $forceRebuild = false, $callbacks=[], $params=[]){

      $version = static::wrap($image);
      $version->transformationClass = $transformationClass;
      $version->callbacks = $callbacks;

      $path = $version->relativePath();

      $filesystem = $version->getFilesystem();

      if($forceRebuild && $filesystem->has($path)){
        $filesystem->delete($path);
      }

      if(!$filesystem->has($path))
        $version->buildNewImage($params);

      return $version;
    }

    public function className(){
      $className = $this->transformationClass;

      if (class_exists($className))
        return $className;

      if ($namespace = \Config::get('image.versions.namespace', null)) {
      // if(isset($this->object->transformationNamespace) && !empty($namespace = $this->object->transformationNamespace)){
        $className =  sprintf("%s\%s",$namespace, $className);
      }

      if (class_exists($className))
        return $className;

      throw new \igaster\imageVersions\Exceptions\TransformationNotFound($className);
    }

    public function getFilesystem(){
      $disk = \Config::get('image.versions.disk', null);

      if($disk){
        return Storage::disk($disk);
      }

      $adapter = new \League\Flysystem\Adapter\Local(public_path());
      $filesystem = new \League\Flysystem\Filesystem($adapter);
      return $filesystem;

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
      $root_url = \Config::get('image.versions.root_url', '');
      return $root_url.'/'.$this->relativePath();
    }

    public function buildNewImage($params = []){        

      $filesystem = $this->getFilesystem();

      $stream = $filesystem->read($this->object->relativePath());
      $image = \Image::make($stream);

      $transformationClass = $this->className();
      $transformation = new $transformationClass();
      if (!method_exists($transformation, 'apply')) {
        throw new \igaster\imageVersions\Exceptions\missingApplyMethod($this->versionName());
      }

      foreach ($this->callbacks as $callback) {
        $callback[0]($image, ...$callback[1]);
      }

      $transformation->apply($image, ...$params);
      $transformation->onSaving($image);
      $transformation->onSaved($this);

      $jpg = (string) $image;
      $filesystem->write($this->relativePath(), $jpg);
    }

}