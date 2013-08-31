<?php namespace Indatus\ActiveResource;

/**
 * Collection class returned from ActiveResource when
 * a colleciton of results is requested
 *
 * @author Brian Webb <bwebb@indatus.com>
 */
class ActiveResourceCollection implements \Iterator {

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



  public function __construct( $givenArray ) {
    $this->collection = $givenArray;
  }

  function rewind() {
    return reset($this->collection);
  }

  function current() {
    return current($this->collection);
  }

  function key() {
    return key($this->collection);
  }

  function next() {
    return next($this->collection);
  }

  function valid() {
    return key($this->collection) !== null;
  }

  function size(){
    return count($this->collection);
  }

  function first(){
    return (empty($this->collection) ? null : $this->collection[0]);
  }

  function last(){
    return (empty($this->collection) ? null : $this->collection[count($this->collection)-1]);
  }

  function toJson(){
    $data = array_merge(array('collection' => $this->collection), array('meta' => $this->metaData));
    return json_encode($data);
  }

}//end class