<?php namespace Indatus\ActiveResource;

/**
 * Collection class returned from ActiveResource when
 * a colleciton of results is requested
 *
 * @author Brian Webb <bwebb@indatus.com>
 */
class ActiveResourceCollection implements \Iterator
{
    /**
     * Var to hold the actual source array collection
     *
     * @var array
     */
    private $collection;
    
    /**
     * Associative array of metadata related to the
     * collection
     *
     * @var array
     */
    public $metaData = array();
    
    public function __construct($givenArray)
    {
        $this->collection = $givenArray;
    }
    
    public function rewind()
    {
        return reset($this->collection);
    }
    
    public function current()
    {
        return current($this->collection);
    }
    
    public function key()
    {
        return key($this->collection);
    }
    
    public function next()
    {
        return next($this->collection);
    }
    
    public function valid()
    {
        return key($this->collection) !== null;
    }
    
    public function size()
    {
        return count($this->collection);
    }
    
    public function first()
    {
        return (empty($this->collection) ? null : $this->collection[0]);
    }
    
    public function last()
    {
        return (empty($this->collection) ? null : $this->collection[count($this->collection)-1]);
    }
    
    public function toArray()
    {
        $entities = array();
        foreach ($this->collection as $entity) {
            $entities[] = $entity->attributes;
        }
    
        return array_merge(array('collection' => $entities), array('meta' => $this->metaData));
    }
    
    public function toJson()
    {
        return json_encode($this->toArray());
    }
}//end class
