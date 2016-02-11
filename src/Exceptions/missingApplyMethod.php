<?php namespace igaster\imageVersions\Exceptions;

class missingApplyMethod extends \Exception{

	// Redefine the exception so message isn't optional
	public function __construct($transformationClass, $code = 0, Exception $previous = null) {

        $message = "You should implement 'apply()' at '$transformationClass' class";

		parent::__construct($message, $code, $previous);
	}


}