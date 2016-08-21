<?php namespace phony;

require_once('phony_implementation.php');

class phony
{
  protected static $phony;

  public static function __get_phony()
  {
    if (empty(self::$phony))
      self::$phony = new phony_implementation();

    return self::$phony;
  }

  public static function __callStatic($name, $arguments)
  {
    var_dump("calling $name");
    return call_user_func_array([self::__get_phony(), $name], $arguments);
  }
}

function phony()
{
  return phony::__get_phony();
}

require_once('rewrite.php');
require_once('include_hook.php');
require_once('internal.php');
