<?php namespace igaster\imageVersions;

// Your model should use this Trait and implement relativePath() function

trait ImageVersionsTrait
{

	/* 	Implement relativePath() in your Eloquent Model that wraps the Image.
		returns the relative (to public) path to image. ( path/to/image.jpg ) 
		example:
	
			public function relativePath() {
				return 'images/'.$this->filename;
			}
	*/
	abstract public function relativePath();

    public function url(){
      $root_url = \Config::get('image.versions.root_url', '');
      return $root_url.'/'.$this->relativePath();
    }

	public function absolutePath(){
		return public_path($this->relativePath());
	}

	private $callback_before = [];


	public function beforeTransformation($callback, ...$params){
		$this->callback_before[] = [$callback, $params];
		return $this;
	}

	public function version($transformation, ...$params){
		return Version::decorate($this, $transformation, false, $this->callback_before, $params);
	}

	public function rebuildVersion($transformation, ...$params){
		return Version::decorate($this, $transformation, true, $this->callback_before, $params);
	}
}