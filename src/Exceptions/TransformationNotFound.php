<?php namespace igaster\imageVersions\Exceptions;

class TransformationNotFound extends \Exception{

	public function __construct($transformationClass, $code = 0, Exception $previous = null) {

        $message = "Image Transformation '$transformationClass' class not found";

		parent::__construct($message, $code, $previous);
	}


}