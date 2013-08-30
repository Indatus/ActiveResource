<?php namespace Indatus\ActiveResource\Facades;
 
use Illuminate\Support\Facades\Facade;
 
class ActiveResource extends Facade {
 
  /**
   * Get the registered name of the component.
   *
   * @return string
   */
  protected static function getFacadeAccessor() { return 'active-resource'; }
 
}