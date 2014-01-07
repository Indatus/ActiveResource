<?php
include __DIR__.'/../vendor/autoload.php';

use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Indatus\ActiveResource\ActiveResourceServiceProvider;

abstract class ActiveResourceTests extends PHPUnit_Framework_TestCase
{

    /**
     * The IoC Container
     *
     * @var Container
     */
    protected $app;

    /**
     * Set up the tests
     *
     * @return void
     */
    public function setUp()
    {
        $this->app = new Container;

        // Laravel classes --------------------------------------------- /

        $this->app['files']   = new Filesystem;
        $this->app['config']  = $this->getConfig();

        // ActiveResource classes ------------------------------------------- /

        $serviceProvider = new ActiveResourceServiceProvider($this->app);
        $this->app = $serviceProvider->bindClasses($this->app);

    }

    /**
     * Tears down the tests
     *
     * @return void
     */
    public function tearDown()
    {
        Mockery::close();
    }


    ////////////////////////////////////////////////////////////////////
    ///////////////////////////// DEPENDENCIES /////////////////////////
    ////////////////////////////////////////////////////////////////////


    /**
     * Mock the Config component
     *
     * @return Mockery
     */
    protected function getConfig($options = array())
    {
        $config = Mockery::mock('Illuminate\Config\Repository');
        $config->shouldIgnoreMissing();

        foreach ($options as $key => $value) {
            $config->shouldReceive('get')->with($key)->andReturn($value);
        }

        // Drivers
        $config->shouldReceive('get')->with('cache.driver')->andReturn('file');
        $config->shouldReceive('get')->with('database.default')->andReturn('mysql');
        $config->shouldReceive('get')->with('remote.default')->andReturn('production');
        $config->shouldReceive('get')->with('remote.connections')->andReturn(array('production' => array(), 'staging' => array()));
        $config->shouldReceive('get')->with('session.driver')->andReturn('file');

        // ActiveResource
        $config->shouldReceive('get')->with('rocketeer::connections')->andReturn(array('production', 'staging'));
        $config->shouldReceive('get')->with('rocketeer::remote.application_name')->andReturn('foobar');
        $config->shouldReceive('get')->with('rocketeer::remote.keep_releases')->andReturn(1);
        $config->shouldReceive('get')->with('rocketeer::remote.permissions')->andReturn(array(
            'permissions' => 755,
            'webuser' => array('user' => 'www-data', 'group' => 'www-data')
        ));
        $config->shouldReceive('get')->with('rocketeer::remote.permissions.files')->andReturn(array('tests'));
        $config->shouldReceive('get')->with('rocketeer::remote.root_directory')->andReturn(__DIR__.'/server/');
        $config->shouldReceive('get')->with('rocketeer::remote.shared')->andReturn(array('tests/meta'));
        $config->shouldReceive('get')->with('rocketeer::stages.default')->andReturn(null);
        $config->shouldReceive('get')->with('rocketeer::stages.stages')->andReturn(array());

        // SCM
        $config->shouldReceive('get')->with('rocketeer::scm.branch')->andReturn('master');
        $config->shouldReceive('get')->with('rocketeer::scm.repository')->andReturn('https://github.com/Anahkiasen/rocketeer.git');
        $config->shouldReceive('get')->with('rocketeer::scm.scm')->andReturn('git');

        return $config;
    }

    /**
     * Swap the current config
     *
     * @param  array $config
     *
     * @return void
     */
    protected function swapConfig($config)
    {
        $this->app['active-resource.active-resource']->disconnect();
        $this->app['config'] = $this->getConfig($config);
    }
}
