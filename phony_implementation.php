<?php namespace phony;

require_once('phony_extend.php');

class phony_implementation extends phony_extend
{
  private $extend;

  public function __Call($name, $arguments)
  {
    if (method_exists($this, $name))
    {
      $this->FilterFriends();
      return call_user_func_array([$this, $name], $arguments);
    }

    return parent::__Call($name, $arguments);
  }
}
