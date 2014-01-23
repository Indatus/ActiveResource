<?php

    /**
     * Function to clean up any temp files written for a request
     *
     * @return void
     */
    protected function doPostRequestCleanUp()
    {
        while (count($this->postRequestCleanUp) > 0) {
            $f = array_pop($this->postRequestCleanUp);
            if (file_exists($f)) {
                unlink($f);
            }
        }
    }//end cleanUp


    /**
     * Function to create a Guzzle HTTP request
     *
     * @param  string $baseUri        The protocol + host
     * @param  string $path           The URI path after the host
     * @param  string $http_method    The HTTP method to use for the request (GET, PUT, POST, DELTE etc.)
     * @param  array  $requestHeaders Any additional headers for the request
     * @return
     */
    protected static function createRequest($baseUri, $path, $http_method = 'GET', $requestHeaders = array())
    {
        $client = new Client($baseUri);

        if (!in_array(strtolower($http_method), array('get', 'put', 'post', 'patch', 'delete', 'head'))) {
            throw new Exception("Invalid HTTP method");
        }

        $method = strtolower($http_method);
        $method = $method == 'patch' ? 'put' : $method; //override patch calls with put

        if (static::$httpMethodParam != null && in_array($method, array('put', 'post', 'patch', 'delete'))) {
            $request = $client->post($path);
            $request->setPostField(static::$httpMethodParam, strtoupper($method));
        } else {
            $request = $client->{$method}($path);
        }

        if (isset(self::$authUser) && isset(self::$authPass)) {
            $request->setAuth(self::$authUser, self::$authPass);
        }

        foreach ($requestHeaders as $header => $value) {
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
        if (isset(static::$resourceName)) {
            return static::$resourceName;
        }

        $full_class_arr = explode("\\", get_called_class());
        $klass = end($full_class_arr);
        static::$resourceName = $klass;

        return $klass;
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
        if (isset(static::$uri)) {
            return static::$uri;
        }

        $uri = Inflector::pluralize(
            Inflector::tableize(
                self::getResourceName()
            )
        );

        $uriResult = array();
        if (!empty(static::$nestedUnder)) {
            $nesting = array_map(
                function ($item) {
                    return explode(':', trim($item));
                },
                explode(',', static::$nestedUnder)
            );


            foreach ($nesting as $nest) {
                list($klass, $entityIdSegment) = $nest;
                if (!is_numeric($entityIdSegment)) {
                    $entityIdSegment = ":$entityIdSegment";
                }

                $entityTypeSegment = Inflector::pluralize(Inflector::tableize($klass));
                $uriResult[] = $entityTypeSegment;
                $uriResult[] = $entityIdSegment;

                $uri = implode("/", $uriResult) . "/$uri";
            }
        }

        return "/$uri";
    }


    /**
     * Function to get the URI with placeholders for data
     * that a GET request should be made to in order to retreive
     * a collection of Entities
     *
     * @param  $options Array of options to replace placeholders with
     * @return string
     */
    protected static function getCollectionUri($options = array())
    {
        $uri = self::getUri();
        foreach ($options as $key => $value) {
            $uri = str_replace($key, $value, $uri);
        }

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
    protected static function getInstanceUri($options = array())
    {
        $uri = implode("/", array(self::getUri(), ':id'));
        foreach ($options as $key => $value) {
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
    protected static function getCreateUri($options = array())
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
    protected static function getUpdateUri($options = array())
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
    protected static function getDeleteUri($options = array())
    {
        return self::getInstanceUri($options);
    }



    /**
     * Function to set the entities properties on the
     * request object taking into account any properties that
     * are read only etc.
     *
     * @param  Guzzle\Http\Message\RequestInterface $request API request object
     */
    protected function setPropertysOnRequest(&$request)
    {
        $cantSet = array_map('trim', explode(',', static::$readOnlyFields));

        //set the property attributes
        foreach ($this->properties as $key => $value) {
            if (in_array($key, self::getFileFields())) {
                $request->addPostFile($key, $value);
            } else {
                if (!in_array($key, $cantSet)) {
                    $request->setPostField($key, $value);
                }
            }
        }
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
     * @param  array  $getParams       Array of additional querystrig / GET parameters
     * @return \Indatus\ActiveResource\Responses\Collection
     */
    public static function findAll(
        $findConditions = array(),
        $logicalOperator = null,
        $orderBy = null,
        $orderDir = null,
        $getParams = array()
    ) {
        //send the request
        $request = self::createRequest(static::$baseUri, self::getCollectionUri(), 'GET');

        //add in request params
        if (!empty($findConditions) || !empty($getParams)) {
            $query = $request->getQuery();

            foreach ($getParams as $param => $val) {
                $query->add($param, $val);
            }

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

            if ($logicalOperator != null) {
                $query->add(self::$logicalOperator, $logicalOperator);
            }

            if ($orderBy != null) {
                $query->add(self::$logicalOperator, $orderBy);
            }

            if ($orderDir != null) {
                $query->add(self::$orderDir, $orderDir);
            }
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
        $collection = new Collection($records);

        //add in the meta data that also gets returned
        $collection->metaData = array_diff_key($data, array_flip((array) array(self::$collectionKey)));

        return $collection;
    }


    /**
     * Function to find an instance of an Entity record
     *
     * @param  int      $id          The primary identifier value for the record
     * @param  array    $getParams   Array of GET parameters to pass
     * @return ActiveResource        An instance of the entity requested
     */
    public static function find($id, $getParams = array())
    {
        $instance = null;

        $request = self::createRequest(
            static::$baseUri,
            self::getInstanceUri(array(':id' => $id)),
            'GET'
        );

        //handle error saving
        $request->getEventDispatcher()->addListener(
            'request.error',
            //instance must be passed by reference since it's in a call back
            function (\Guzzle\Common\Event $event) use (&$instance) {
                if ($event['response']->getStatusCode() == 404) {
                    // Stop other events from firing
                    $event->stopPropagation();

                    //not found
                    $instance = false;
                } else if($event['response']->getStatusCode() == 500) {
                    $event->stopPropagation();
                    //not found
                    $instance = false;
                }
            }
        );

        $query = $request->getQuery();

        //add in any GET params
        foreach ($getParams as $param => $val) {
            $query->add($param, $val);
        }

        //send the request
        $response = self::sendRequest($request);

        if ($response->getStatusCode() == 404 || $instance === false) {
            return null;
        }

        $data = self::parseResponseToData($response);
        $klass = self::getResourceName();
        $instance = new $klass($data);

        return $instance;
    }

    /**
     * Function to handle creating or updating
     * an instance
     *
     * @return Boolean  Success of the save operation
     */
    public function save()
    {
        if ($this->getId() === false) {
            return $this->create();
        }

        return $this->update();
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
            'POST'
        );

        //handle error saving & any errors given
        $request->getEventDispatcher()->addListener(
            'request.error',
            function (\Guzzle\Common\Event $event) {
                if ($event['response']->getStatusCode() == 422) {
                    // Stop other events from firing
                    $event->stopPropagation();

                    //get the errors and set them
                    $response = self::parseResponseStringToObject($event['response']->getBody(true));
                    if (property_exists($response, 'errors')) {
                        $this->errors = $response->errors;
                    }

                    //return false create save failed
                    $this->doPostRequestCleanUp();
                    return false;
                }
            }
        );


        //set the property attributes
        $this->setPropertysOnRequest($request);

        //send the request
        $response = self::sendRequest($request);

        //handle clean response with errors
        if ($response->getStatusCode() == 422) {
            //get the errors and set them
            $result = self::parseResponseStringToObject($response->getBody(true));
            if (property_exists($result, 'errors')) {
                $this->errors = $result->errors;
            }
            $this->doPostRequestCleanUp();

            return false;
        }//end if

        //get the response and inflate from that
        $data = self::parseResponseToData($response);
        $this->inflateFromArray($data);

        $this->doPostRequestCleanUp();
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
            'PATCH'
        );

        //handle error saving & any errors given
        $request->getEventDispatcher()->addListener(
            'request.error',
            function (\Guzzle\Common\Event $event) {
                if ($event['response']->getStatusCode() == 422) {
                    // Stop other events from firing
                    $event->stopPropagation();

                    //get the errors and set them
                    $response = self::parseResponseStringToObject($event['response']->getBody(true));
                    if (property_exists($response, 'errors')) {
                        $this->errors = $response->errors;
                    }

                    //return false create save failed
                    $this->doPostRequestCleanUp();

                    return false;
                }
            }
        );

        //set the property attributes
        $this->setPropertysOnRequest($request);

        //send the request
        $response = self::sendRequest($request);

        //handle clean response with errors
        if ($response->getStatusCode() == 422) {
            //get the errors and set them
            $result = self::parseResponseStringToObject($response->getBody(true));
            if (property_exists($result, 'errors')) {
                $this->errors = $result->errors;
            }
            $this->doPostRequestCleanUp();

            return false;
        }//end if


        //get the response and inflate from that
        $result = self::parseResponseToData($response);

        $this->inflateFromArray($result);

        $this->doPostRequestCleanUp();

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
            'DELETE'
        );

        //send the request
        $response = self::sendRequest($request);

        $this->doPostRequestCleanUp();

        if ($response->getStatusCode() == 200) {
            return true;
        }

        return false;
    }//end destroy


}//end class
