<?php namespace igaster\imageVersions\Tests\App;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Photo extends Eloquent
{
	use \igaster\imageVersions\ImageVersionsTrait;

    protected $table = 'photos';
	protected $guarded = [];
	public $timestamps = false;

	public function relativePath(){
		return $this->filename;
	}
}