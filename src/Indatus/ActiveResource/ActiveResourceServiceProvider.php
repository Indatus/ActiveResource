<?php namespace Indatus\ActiveResource;

use Illuminate\Support\ServiceProvider;

class ActiveResourceServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->package('indatus/active-resource');
    }

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app['active-resource'] = $this->app->share(function($app)
		{
			return new ActiveResource;
		});

		$this->app->booting(function()
		{
		 	$loader = \Illuminate\Foundation\AliasLoader::getInstance();
		  	$loader->alias('ActiveResource', 'Indatus\ActiveResource\ActiveResource');
		});

	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('active-resource');
	}

}