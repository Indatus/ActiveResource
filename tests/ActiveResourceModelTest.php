<?php

use Indatus\ActiveResource\Facades\ActiveResource;

class ActiveResourceModelTest extends ActiveResourceTests
{


    public function testAppMake()
    {
        $ar = $this->app->make('active-resource');
        $this->assertEquals($ar->testMeOut(), "hey, it worked");
    }

    public function testFacade()
    {
        $array = array(1,2,3);
        ActiveResource::$guarded = array(1,2,3);
        $this->assertEquals(ActiveResource::$guarded, $array);
    }

    // public function testGetter(){}

    // public function testSetter(){}

    // public function testInflateFromArray(){}

    // public function testConstructorInflateFromArray(){}

    // public function testBasicFindAll(){}

    // public function testFindAllWithConditions(){}

    // public function testFullFindAll(){}

    // public function testFindById(){}

    // public function testGetId(){}

    // public function testSaveFunction(){}

    // public function testCreateShouldSave(){}

    // public function testCreateShouldFail(){}

    // public function testUpdateShouldSave(){}

    // public function testUpdateShouldFail(){}

    // public function testDestroy(){}
}