<?php

use AspectMock\Test as am;

class ActiveResourceRequestTest extends PHPUnit_Framework_TestCase
{
    protected function tearDown()
    {
        //am::clean(); // remove all registered test doubles
    }


    public function testCreateRequest()
    {
        //create request
        //
        //get, post, put / patch, delete
        //
        //set auth
        //
        //set headers
    }


    public function testParseResponseData(){}

    public function testSetTransportLanguage(){}

    public function testParseResponseStringToObj(){}

    public function testSendRequest(){}

}