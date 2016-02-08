<?php

use igaster\imageVersions\Tests\TestCase\TestCaseWithDatbase;
use Orchestra\Testbench\TestCase;


use igaster\imageVersions\Tests\App\Photo;
use igaster\imageVersions\Version;

use igaster\imageVersions\Tests\App\Transformations\v200x200;

class ExampleTest extends TestCaseWithDatbase
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
            'Image' => Intervention\Image\Facades\Photo::class,
        ];
    }

    // -----------------------------------------------
    //   Global Setup(Run Once)
    // -----------------------------------------------

    public static function setUpBeforeClass() {
        parent::setUpBeforeClass();
        // Your Code here...
    }

    public static function tearDownAfterClass(){
        // Your Code here...
        parent::tearDownAfterClass();
    }

    // -----------------------------------------------
    //  Setup Database (Run before each Test)
    // -----------------------------------------------

    public function setUp()
    {
        parent::setUp();

        // set the public path to this directory
        App::bind('path.public', function() {
            return __DIR__.'/public';
        });

        // -- Set  migrations
        \Schema::create('photos', function ($table) {
            $table->increments('id');
            $table->string('filename');
        });

        Photo::create(['id' => 1, 'filename' => 'image1.jpg']);
        Photo::create(['id' => 2, 'filename' => 'image2.jpg']);
        Photo::create(['id' => 3, 'filename' => 'subfolder/image3.jpg']);
    }

    public function _tearDown() {
        \Schema::drop('photos');
        parent::teadDown();
    }

    // -----------------------------------------------
    //  Tests
    // -----------------------------------------------

    public function testSetup() {
        foreach (Photo::all() as $image) {
            $this->assertFileExists($image->absolutePath());
        }
    }

    public function testDecoratable() {
        $image = Photo::find(3);
        $this->assertInstanceOf(Photo::class, $image);
        $version = $image->version(v200x200::class);

        $this->assertInstanceOf(Version::class, $version);
        $this->assertEquals('v200x200', $version->versionName());
    }

    public function testPaths() {
        $this->assertEquals('v200x200/image1.jpg',              Photo::find(1)->version(v200x200::class)->relativePath());
        $this->assertEquals('subfolder/v200x200/image3.jpg',    Photo::find(3)->version(v200x200::class)->relativePath());
    }

    public function testCreateFolders() {
        \File::deleteDirectory(public_path('subfolder/v200x200'));
        \File::deleteDirectory(public_path('v200x200'));

        $this->assertFileNotExists(public_path('v200x200'));
        $this->assertFileNotExists(public_path('subfolder/v200x200'));

        Photo::find(1)->version(v200x200::class);
        Photo::find(3)->version(v200x200::class);

        $this->assertFileExists(public_path('v200x200'));
        $this->assertFileExists(public_path('subfolder/v200x200'));
    }

    public function testCreateFiles() {
        \File::deleteDirectory(public_path('subfolder/v200x200'));
        \File::deleteDirectory(public_path('v200x200'));

        $image1 = Photo::find(1)->version(v200x200::class);
        $image3 = Photo::find(3)->version(v200x200::class);

        $this->assertFileExists($image1->absolutePath());
        $this->assertFileExists($image3->absolutePath());
    }

}