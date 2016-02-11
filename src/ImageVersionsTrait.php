<?php namespace igaster\imageVersions;

// Your model should use this Trait and implement relativePath() function

trait ImageVersionsTrait
{

	abstract public function relativePath();

	/* 	Implement in your Eloquent Model that wraps the Image.
		returns the relative (to public) path to image. ( path/to/image.jpg ) 
		example:

		public function relativePath() {
			return 'images/'.$this->filename;
		}
	*/

	public function url(){
		return '/'.$this->relativePath();
	}

	public function absolutePath(){
		return public_path($this->relativePath());
	}

	public function version($transformation, ...$params){
		return \igaster\imageVersions\Version::apply($this, $transformation, $params);
	}
}