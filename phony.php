<?php namespace phony;

require_once('phony_implementation.php');

class phony
{
  protected $phony;

  protected function __get_phony()
  {
    if (empty($this->phony))
      $this->phony = new phony_implementation();

    return $this->phony;
  }

  public static function __callStatic($name, $arguments)
  {
    return call_user_method_array([$this->phony, $name], $arguments);
  }
}


require_once('include_hook.php');
require_once('rewrite.php');
require_once('internal.php');
