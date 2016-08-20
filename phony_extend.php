<?php namespace phony;

class phony_extend
{
  private $methods = [];

  protected function add($name, $is_public, $fn_command)
  {
    if (!empty($this->methods[$name]))
      throw Exception("Method already exists. Refuse to override");

    $this->methods[$name] = $fn_command;
  }

  public function __Call($name, $arguments)
  {
    if (empty($this->methods[$name]))
      throw Exception("phony::{$name} not defined");

    $method = $this->methods[$name];

    if (is_string($method))
      return call_user_method_array($method, $arguments);

    throw Exception("phony_extend support only string method. todo");
  }

  protected function FilterFriends()
  {
    debug_print_backtrace();
  }
}
