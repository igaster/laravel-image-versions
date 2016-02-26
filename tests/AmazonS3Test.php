<?php

/*----------------------------[ Instructions ] -----------------------------
 *
 *   If you want to run these tests then you should setup an Amazon S3 bucket.
 *   
 *   1. Open phpunit.xml and set your account credentials in env variables
 *   2. On your bucket's root create a folder 'tests' and upload file 'tests\public\image1.jpg'
 *   3. Run your tests!
 *   
 *   To execute only the tests on local filesystem you should:
 *   
 *       phpunit tests/LocalTest.php
 *
 *--------------------------------------------------------------------------*/

use igaster\imageVersions\Tests\TestCase\TestCaseWithDatbase;
use Orchestra\Testbench\TestCase;

use igaster\imageVersions\Tests\App\Photo;
use igaster\imageVersions\Version;

use igaster\imageVersions\Tests\App\Transformations\v200x200;
use igaster\imageVersions\Tests\App\Transformations\vParameters;
use igaster\imageVersions\Tests\App\Transformations\vMissingMethod;

class AmazonS3Test extends TestCaseWithDatbase
{
    // -----------------------------------------------
    //   add Service Providers & Facades
    // -----------------------------------------------

    protected function getPackageProviders($app) {
        return [
            Intervention\Image\ImageServiceProvider::class,
        ];
    }


    protected function getPackageAliases($app) {
        return [
            'Image' => Intervention\Image\Facades\Image::class
        ];
    }

    // -----------------------------------------------
    //  Setup Database (Run before each Test)
    // -----------------------------------------------

    public function setUp()
    {
        parent::setUp();

        Config::set('filesystems.disks.s3.key',    env('S3_KEY',      'key'));
        Config::set('filesystems.disks.s3.secret', env('S3_SECRET',   'secret'));
        Config::set('filesystems.disks.s3.region', env('S3_REGION',   'region'));
        Config::set('filesystems.disks.s3.bucket', env('S3_BUCKET',   'bucket'));

        Config::set('image.versions.disk',         's3');
        Config::set('image.versions.root_url',     env('S3_ENDPOINT', 'endpoint'));
        Config::set('image.versions.namespace',    'igaster\imageVersions\Tests\App\Transformations');

        // -- Set  migrations
        Schema::create('photos', function ($table) {
            $table->increments('id');
            $table->string('filename');
        });

        Photo::create(['id' => 1, 'filename' => 'tests/image1.jpg']);
    }

    public function _tearDown() {
        Schema::drop('photos');
        parent::teadDown();
    }

    // -----------------------------------------------
    //  Tests
    // -----------------------------------------------

    public function test_create_file() {
        Storage::disk('s3')->deleteDir('tests/v200x200');
        $img = Photo::find(1)->version(v200x200::class);
        $this->assertTrue(Storage::disk('s3')->has('tests/v200x200/image1.jpg'));
    }

    public function test_Retreive_Saved_File_Instead_Of_Creating_New() {
        Storage::disk('s3')->deleteDir('tests/vParameters');
        Photo::find(1)->version(vParameters::class);
        $this->expectOutputString('');
        Photo::find(1)->version(vParameters::class, 'Image Created!');
    }

}