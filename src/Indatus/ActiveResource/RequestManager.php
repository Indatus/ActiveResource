<?php

namespace Indatus\ActiveResource;

use Illuminate\Container\Container;

class RequestManager
{

    /**
     * The IoC Container
     *
     * @var Container
     */
    protected $app;


    /**
     * Build a new RequestManager
     *
     * @param Container $app
     */
    public function __construct(Container $app)
    {
        $this->app = $app;
    }


    /**
     * Get an option from ActiveResource's config file
     *
     * @param  string $option
     *
     * @return mixed
     */
    public function getOption($option)
    {
        return $this->app['config']->get('active-resource::'.$option);
    }



}