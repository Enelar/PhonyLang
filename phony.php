<?php namespace phony;

require_once('phony_implementation.php');

class phony
{
  protected static $phony;

  protected static function __get_phony()
  {
    if (empty(self::$phony))
      self::$phony = new phony_implementation();

    return self::$phony;
  }

  private static function __unescape_methodname($name)
  {
    if (substr($name, 0, 2) == "__")
      return substr($name, 2);

    return $name;
  }

  public static function __callStatic($name, $arguments)
  {
    var_dump("calling $name");
    $method = self::__unescape_methodname($name);
    return call_user_func_array([self::__get_phony(), $method], $arguments);
  }
}

function phony()
{
  return new phony();
}

require_once('include_hook.php');
require_once('rewrite.php');
require_once('internal.php');
