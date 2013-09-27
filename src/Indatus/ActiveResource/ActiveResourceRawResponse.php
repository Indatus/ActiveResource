<?php namespace Indatus\ActiveResource;

/**
 * Result class returned from ActiveResource when
 * a raw request is initiated
 *
 * @author Brian Webb <bwebb@indatus.com>
 */
class ActiveResourceRawResponse
{
    /**
     * Var to hold XML or JSON response converted
     * to an object
     *
     * @var object
     */
    private $response = null;

    /**
     * Var to hold any errors returned
     * 
     * @var array
     */
    private $errors = array();

    /**
     * Var to tell if the request was successful
     * 
     * @var boolean
     */
    public $success = false;

    /**
     * Constructor
     * 
     * @param boolean $successful 
     * @param Object  $response  
     * @param array   $errors     
     */
    public function __construct($successful = false, $response = null, $errors = array())
    {
        $this->success = $successful;
        $this->response = $response;
        $this->errors = $errors;
    }

    /**
     * Getter for errors
     */
    public function errors()
    {
        return $this->errors;
    }

    /**
     * Getter for response
     */
    public function response()
    {
        return $this->repsonse;
    }
}
