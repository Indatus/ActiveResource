## PHP ActiveResource Library

This is a PHP library for accessing REST APIs in an ActiveResource style of coding.   The benefit is easier use of REST APIs in a fast and clean programming interface.

The library follows convention over configuration. So you should be able to get up to speed consuming a REST API in a very short time.  

#### Installation

You can install the library via Composer by adding the following line to the **require** block of your composer.json file:

````
"indatus/active-resource": "dev-master"
````

Next run `composer update` or `composer install`

### Examples

#### Base Class

It may be a good idea to create a base class from which your models will extend that contains settings that will be shared across everything.

````
<?php

class ActiveResourceBase extends ActiveResource
{

	//add any global AR configs here

    public function __construct($attributes = array())
    {
        parent::__construct($attributes);
    }
}

ActiveResourceBase::$baseUri = "http://example.com";

````

Now create an entity (this is the minimum code you'll need):

````
<?php

class Product extends ActiveResourceBase
{
    
}

````

The library uses convention over configuration, so it will infer what the URI should be based on your class name.  In the example of 'Product' the URI will be assumed to be /products.  

#### CRUD Operations

Now that you have an ActiveResource class you can use it with CRUD operations as you may expect you would with an ORM.

````
$p = Product::find(1);
$p->name = "My Product";

if ($p->save()) {
   echo "Saved!";
} else {
   echo "Error: ". implode("\n", $p->errors());
}
````

What all can you do? Here's some basics:

````
//find by id
$product = Product::find(1);

//update an attribute
$product->attribute = 'foo';

//update several attributes
$product->updateAttributes(array('name' => 'test', 'description' => 'some desc'));

//save the instance
$product->save(); //returns boolean

//get any errors from an invalid save
$product->errors();

//----------

//create a new object;
$product = new Product(array('name' => 'test', 'description' => 'some desc'));
$product->save();

//destroy an object;
$product->destroy();

//----------

//Find all with conditions

$conditions = array();
$conditions[] = array(
    Product::$searchProperty => 'some_property_1',
    Product::$searchOperator => '=',
    Product::$searchValue => 'value'
);

$conditions[] = array(
    Product::$searchProperty => 'some_property_2',
    Product::$searchOperator => '>=',
    Product::$searchValue => '123'
);

$conditions[] = array(
    Product::$searchProperty => 'some_property_3',
    Product::$searchOperator => 'LIKE',
    Product::$searchValue => '%partial name'
);

$collection = Product::findAll(
	$conditions,
	Product::$searchOperatorAnd,
	'some_property_1',
	Product::$orderDirDesc
);

````

Remember that the library uses convention over configuration, so if you want to override something you likely just need to set the protected property.

For example, on a Product the URI will be inflected to be /products.  Just like Person would be /people.  If you want to have something different you could do it 2 ways.  Say you wanted to make **Product** use /my_cool_products. You could do this by setting the protected static variable `$resourceName` to 'MyCoolProduct'.  Or you could just set the protected static variable `$uri` to be '/my_cool_products';

#### Documentation

Please note that this README is a work in progress, hopefully there can be more examples added as to the flexibility and things you can do with the library.  Until then please see the *doc** directory for generated documentation.