<?php namespace phony;

require_once('phony_extend.php');

class phony_implementation extends phony_extend
{
  private $extend;

  private function __unescape_methodname($name)
  {
    if (substr($name, 0, 2) == "__")
      return substr($name, 2);

    return $name;
  }

  public function __Call($method, $arguments)
  {
    $name = $this->__unescape_methodname($method);

    if (method_exists($this, $name))
    {
      $this->FilterFriends();
      return call_user_func_array([$this, $name], $arguments);
    }

    return parent::__Call($name, $arguments);
  }
}
