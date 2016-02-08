<?php namespace igaster\imageVersions;

// Your transformation classes should implement this interface

use Imagick;

interface TransformationInterface {
    public static function applyTransformations(Imagick $Image);
}