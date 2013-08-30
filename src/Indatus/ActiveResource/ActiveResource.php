<?php namespace Indatus\ActiveResource;

use Guzzle\Http\Client;
use Doctrine\Common\Inflector\Inflector;

/**
 * Base class for interacting with a remote API with an interface
 * that is familiar to the same programming API as Eloquent or more
 * specifically Ardent ORM models.
 *
 * @author Brian Webb <bwebb@indatus.com>
 */
class ActiveResource
{

    /**
     * Request used to interact with remote API
     * 
     * @var Guzzle\Http\Message\RequestInterface
     */
    protected $request;

    /**
     * Protocol + host of base URI to remote API
     * i.e. http://example.com
     * 
     * @var string
     */
    protected static $baseUri;

    /**
     * Property to overwrite the ActiveResource::getResourceName()
     * function with a static value
     * 
     * @var string
     */
    protected static  $resourceName;

    /**
     * Property to overwrite the ActiveResource::getURI()
     * function with a static value of what remote API URI path
     * to hit
     * 
     * @var string
     */
    protected static  $uri;

    /**
     * Username for remote API authentication if required
     * 
     * @var string
     */
    protected static $authUser;

    /**
     * Password for remote API authentication if required
     * @var string
     */
    protected static $authPass;

    /**
     * Transport method of data from remote API
     * @var string
     */
    protected static $transporter = 'json';

    /**
     * Array of instance values 
     * @var array
     */
    protected $properties = array();

    /**
     * Element name that should contain a collection in a 
     * response where more than one result is returned
     * 
     * @var string
     */
    protected static $collectionKey = 'collection';

    /**
     * Name of the parameter key used to contain search
     * rules for fetching collections
     * 
     * @var string
     */
    protected static $searchParameter = 'search';

    /**
     * Name of the parameter key used to identify 
     * an entity property
     * 
     * @var string
     */
    public static $searchProperty = 'property';

    /**
     * Name of the parameter key used to specify 
     * a search rule operator i.e.: = >= <= != LIKE
     * 
     * @var string
     */
    public static $searchOperator = 'operator';

    /**
     * Name of the parameter key used to identify
     * an entity value when searching
     * @var string
     */
    public static $searchValue = 'value';

    /**
     * Name of the parameter key used to identify
     * how search criteria should be joined
     * 
     * @var string
     */
    public static $logicalOperator = 'logical_operator';

    /**
     * Name of the parameter key used to identify
     * the property to order search results by
     * 
     * @var string
     */
    public static $orderBy = 'order_by';

    /**
     * Name of the parameter key used to identify
     * the order direction of search results
     * 
     * @var string
     */
    public static $orderDir = 'order_dir';

    /**
     * Name of the parameter value for specifying 
     * "AND" search rule joining
     * 
     * @var string
     */
    public static $searchOperatorAnd = 'AND';

    /**
     * Name of the parameter value for specifying
     * "OR" search rule joining
     * 
     * @var string
     */
    public static $searchOperatorOr = 'OR';

    /**
     * Name of the parameter value for specifying
     * ascending result ordering
     * 
     * @var string
     */
    public static $orderDirAsc = 'ASC';

    /**
     * Name of the parameter value for specifying 
     * descending result ordering
     * 
     * @var string
     */
    public static $orderDirDesc = 'DESC';

    /**
     * Resource's primary key property
     * 
     * @var string
     */
    protected static $identityProperty = 'id';

    /**
     * Var to hold instance errors
     * 
     * @var array
     */
    private $errors = array();




    /**
     * Constructor used to popuplate the instance with
     * attribute values
     * 
     * @param array $attributes Associative array of property names and values
     */
    public function __construct($attributes=array())
    {
        $this->inflateFromArray($attributes);

    }//end constructor


    /**
     * Magic getter function for accessing instance properties
     * 
     * @param  string $key  Property name
     * @return any          The value stored in the property 
     */
    public function __get($key)
    {
        if ($key === 'attributes'){

            return $this->properties;

        } else {

            if (array_key_exists($key, $this->properties)){
                return $this->properties[$key];
            }

        }
    }


    /**
     * Magic setter function for setting instance properties
     * 
     * @param   string    $key        Property name
     * @param   any       $value      The value to store for the property 
     * @return  void
     */
    public function __set($key, $value)
    {
        $this->properties[$key] = $value;
    }


    /**
     * Function to inflate an instance's properties from an 
     * array of keys and values
     * 
     * @param  array  $attributes   Associative array of properties and values
     * @return void
     */
    public function inflateFromArray($attributes=array())
    {
        foreach ($attributes as $property => $value){
            $this->{$property} = $value;
        }
    }


    /**
     * Function to create a Guzzle HTTP request
     * 
     * @param  string $baseUri        The protocol + host
     * @param  string $path           The URI path after the host
     * @param  array  $requestHeaders Any additional headers for the request
     * @return  
     */
    protected static function createRequest($baseUri, $path, $http_method='GET', $requestHeaders=array())
    {
        $client = new Client($baseUri);

        if (in_array(strtolower($http_method), array('get', 'put', 'post', 'patch', 'delete', 'head'))){
            $method = strtolower($http_method);
            $method = $method == 'patch' ? 'put' : $method; //override patch calls with put
        } else {
            throw new Exception("Invalid HTTP method");
        }

        $request = $client->{$method}($path);

        if (isset(self::$authUser) && isset(self::$authPass)){
            $request->setAuth(self::$authUser, self::$authPass);
        }

        foreach ($requestHeaders as $header => $value){
            $request->setHeader($header, $value);
        }

        //setup how we get data back (xml, json etc)
        self::setTransportLanguage($request);

        return $request;
    }


    /**
     * Function to interpret the URI resource name based on the class called.
     * Generally this would be the name of the class.
     * 
     * @return string   The sub name of the resource
     */
    private static function getResourceName()
    {
        if (isset(self::$resourceName)){ 

            return self::$resourceName;

        } else {

            $full_class_arr = explode("\\", get_called_class());
            $klass = end($full_class_arr);
            self::$resourceName = $klass;
            return $klass;
        }
    }


    /**
     * Function to return the name of the URI to hit based on
     * the interpreted name of the class in question.  For example
     * a Person class would resolve to /people
     * 
     * @return string   The URI to hit
     */
    private static function getURI()
    {
        if (isset(self::$uri)){

            return self::$uri;

        } else {
            $uri = Inflector::pluralize(
                Inflector::tableize(
                    self::getResourceName()
                )
            );
            return "/$uri";
        }
    }


    /**
     * Function to get the URI with placeholders for data
     * that a GET request should be made to in order to retreive 
     * a collection of Entities
     *
     * @param  $options Array of options to replace placeholders with
     * @return string
     */
    protected static function getCollectionUri($options=array())
    {
        $uri = self::getUri();
        return $uri;
    }


    /**
     * Function to get the URI with placeholders for data
     * that a GET request should be made to in order to retreive 
     * an instance of an Entity
     *
     * @param  $options Array of options to replace placeholders with
     * @return string
     */
    protected static function getInstanceUri($options=array())
    {
        $uri = implode("/", array(self::getUri(), ':id'));
        foreach ($options as $key => $value){
            $uri = str_replace($key, $value, $uri);
        }
        return $uri;
    }


    /**
     * Function to get the URI with placeholders for data
     * that a POST request should be made to in order to create
     * a new entity.
     *
     * @param  $options Array of options to replace placeholders with
     * @return string
     */
    protected static function getCreateUri($options=array())
    {
        return self::getCollectionUri($options);
    }


    /**
     * Function to get the URI with placeholders for data
     * that a PUT / PATCH request should be made to in order to
     * update an existing entity.
     *
     * @param  $options Array of options to replace placeholders with
     * @return string
     */
    protected static function getUpdateUri($options=array())
    {
        return self::getInstanceUri($options);
    }


    /**
     * Function to get the URI with placeholders for data
     * that a DELETE request should be made to in order to delete
     * an existing entity.
     *
     * @param  $options Array of options to replace placeholders with
     * @return string
     */
    protected static function getDeleteUri($options=array())
    {
        return self::getInstanceUri($options);
    }


    /**
     * Function to take a response object and convert it
     * into an array of data that is ready for use
     * @param  Guzzle\Http\Message\Response $response Response object from API request
     * @return array           Parsed array of data
     */
    private static function parseResponseToData($response)
    {
        // //convert response data into usable PHP array
        switch (static::$transporter){
            case 'json':
                $data = $response->json();
                break;
            case 'xml':
                $data = $response->xml();
                break;
            default:
                $data = null;
                break;
        }
        
        return $data;
    }


    /**
     * Function to set the language of data transport.  I.e. XML, JSON etc
     * 
     * @param Guzzle\Http\Message\RequestInterface $request Request to set type for
     */
    private static function setTransportLanguage(&$request)
    {
        switch (static::$transporter){
            case 'json':
                $request->setHeader('Accept', 'application/json');
                break;

            case 'xml':
                $request->setHeader('Accept', 'application/xml');
                break;
        }
    }


    /**
     * Function to take a response string (as a string) and depending on 
     * the type of string it is, parse it into an object.
     * 
     * @param  string $responseStr Response string
     * @return object
     */
    private static function parseResponseStringToObject($responseStr)
    {
        $data = null;

        switch (static::$transporter){
            case 'json':
                $data = json_decode($responseStr);
                break;

            case 'xml':
                $data = simplexml_load_string($responseStr);
                break;
        }

        return $data;
    }


    /**
     * Function to wrap the making of a remote API request
     * 
     * @param  Guzzle\Http\Message\RequestInterface $request API request object
     * @return uzzle\Http\Message\Response          API Response
     */
    private static function sendRequest($request)
    {
        return $response = $request->send();
    }


    /**
     * Function to return a collection of remote API data that corresponds to 
     * the particular ActiveResource class referenced and also conforming to the
     * parameters passed in.
     *
     * <code>
     * $results = Company::findAll(
     *      array(
     *          array(
     *              Company::$searchProperty => 'name', 
     *              Company::$searchOperator => 'LIKE', 
     *              Company::$searchValue    => 'Jacobs-Goodwin%'
     *          )
     *      ),
     *      Company::$searchOperatorAnd,
     *      'name',
     *      Company::$orderDirAsc
     *  );
     * </code>
     * 
     * @param  array  $findConditions  An array of arrays containing conditions regarding
     *                                 the filtering of the collection results.  Each array
     *                                 Entry should be associative and contain keys for the 
     *                                 property, operator and value to search by.
     *                                 
     * @param  string $logicalOperator The operator for joining the filter conditions (AND | OR)
     * @param  string $orderBy         The property name results shoudl be ordered by
     * @param  string $orderDir        The direction for ordering results (ASC | DESC)
     * @return \Indatus\ActiveResource\ActiveResourceCollection
     */
    public static function findAll($findConditions=array(), $logicalOperator=null, 
        $orderBy=null, $orderDir=null)
    {
        //send the request
        $request = self::createRequest(static::$baseUri, self::getCollectionUri(), 'GET');

        //add in request params
        if (!empty($findConditions)){

            $query = $request->getQuery();

            $conditionCounter = 0;
            foreach ($findConditions as $condition) {

                $query->add(
                    self::$searchParameter."[$conditionCounter][".self::$searchProperty."]", 
                    $condition[self::$searchProperty]
                );
                $query->add(
                    self::$searchParameter."[$conditionCounter][".self::$searchOperator."]", 
                    $condition[self::$searchOperator]
                );
                $query->add(
                    self::$searchParameter."[$conditionCounter][".self::$searchValue."]", 
                    $condition[self::$searchValue]
                );

                $conditionCounter++;

            }//end foreach $findConditions

            if ($logicalOperator != null) 
                $query->add(self::$logicalOperator, $logicalOperator);

            if ($orderBy != null) 
                $query->add(self::$logicalOperator, $orderBy);

            if ($orderDir != null) 
                $query->add(self::$orderDir, $orderDir);

        }//end if
        
        //send the request
        $response = self::sendRequest($request);

        $data = self::parseResponseToData($response);

        //popuplate the actual result records
        $records = array();
        foreach ($data[self::$collectionKey] as $values) {
            $klass = self::getResourceName();
            $records[] = new $klass($values);
        }

        //create a collection object
        $collection = new ActiveResourceCollection($records);

        //add in the meta data that also gets returned
        $collection->metaData = array_diff_key($data, array_flip((array) array(self::$collectionKey))); 

        return $collection;
    }


    /**
     * Function to find an instance of an Entity record
     * 
     * @param  int $id          The primary identifier value for the record
     * @return ActiveResource   An instance of the entity requested
     */
    public static function find($id)
    {
        $request = self::createRequest(static::$baseUri, 
            self::getInstanceUri(array(':id' => $id)), 'GET');

        //send the request
        $response = self::sendRequest($request);

        $data = self::parseResponseToData($response);
        $klass = self::getResourceName();
        $instance = new $klass($data);
        
        return $instance;
    }


    /**
     * Function to get the instance ID, returns false if there
     * is not one
     * 
     * @return instanceId | false
     */
    private function getId()
    {
        if (array_key_exists(self::$identityProperty, $this->properties)){
            return $this->properties[self::$identityProperty];
        } else {
            return false;
        }
    }


    /**
     * Function to return any errors that
     * may have prevented a save
     * 
     * @return array
     */
    public function errors()
    {
        return $this->errors;
    }


    /**
     * Function to handle creating or updating
     * an instance
     * 
     * @return Boolean  Success of the save operation
     */
    public function save()
    {
        if ($this->getId() === false){
            return $this->create();
        } else {
            return $this->update();
        }
    }


    /**
     * Function to handle the creation of a NEW entity
     * 
     * @return Boolean  Success of the create operation 
     */
    protected function create()
    {
        $request = self::createRequest(
            static::$baseUri, 
            self::getCreateUri(), 
            'POST');

        //handle error saving
        $request->getEventDispatcher()->addListener('request.error', function(\Guzzle\Common\Event $event) {

            if ($event['response']->getStatusCode() == 422) {

                // Stop other events from firing
                $event->stopPropagation();

                //get the errors and set them
                $response = self::parseResponseStringToObject($event['response']->getBody(true));
                if(property_exists($response, 'errors')){
                    $this->errors = $response->errors;
                }

                //return false create save failed
                return false;
            }
        });


        //set the property attributes
        foreach ($this->properties as $key => $value) {
            $request->addPostField($key, $value);
        }

        //send the request
        $response = self::sendRequest($request);

        //get the response and inflate from that
        $data = self::parseResponseToData($response);
        $this->inflateFromArray($data);

        return true;
    
    }//end create


    /**
     * Function to handle updating an existing entity
     * 
     * @return Boolean  Success of the update opeartion
     */
    protected function update()
    {
        $request = self::createRequest(
            static::$baseUri, 
            self::getUpdateUri(array(':'.self::$identityProperty => $this->getId())), 
            'PATCH');

        //handle error saving
        $request->getEventDispatcher()->addListener('request.error', function(\Guzzle\Common\Event $event) {

            if ($event['response']->getStatusCode() == 422) {

                // Stop other events from firing
                $event->stopPropagation();

                //get the errors and set them
                $response = self::parseResponseStringToObject($event['response']->getBody(true));
                if(property_exists($response, 'errors')){
                    $this->errors = $response->errors;
                }

                //return false create save failed
                return false;
            }
        });

        //set the properties in the request
        $query = $request->getQuery();
        foreach ($this->properties as $k => $v) {
            $query->add($k, $v);
        }

        //send the request
        $response = self::sendRequest($request);

        //get the response and inflate from that
        $result = self::parseResponseToData($response);

        $this->inflateFromArray($result);

        return true;
    
    }//end update


    /**
     * Function to delete an existing entity
     * 
     * @return Boolean  Success of the delete operation
     */
    public function destroy()
    {
        $request = self::createRequest(
            static::$baseUri, 
            self::getDeleteUri(array(':'.self::$identityProperty => $this->getId())), 
            'DELETE');

        //send the request
        $response = self::sendRequest($request);

        if ($response->getStatusCode() == 200){
            return true;
        } else{
            return false;
        }
    }//end destroy


}//end class