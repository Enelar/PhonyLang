<?php namespace phony;

class phony_extend
{
  private $methods = [];
  private $variables = [];

  protected function add($name, $is_public, $fn_command)
  {
    if (!empty($this->methods[$name]))
      throw new Exception("Method already exists. Refuse to override");

    $method_described =
    [
      'public' => $is_public,
      'func' => $fn_command,
    ];

    $this->methods[$name] = $method_described;
  }

  public function __Call($name, $arguments)
  {
    if (empty($this->methods[$name]))
    {
      var_dump(array_keys($this->methods));
      throw new Exception("phony::{$name} not defined");
    }

    $method = $this->methods[$name];
    $func = $method['func'];

    if (!$method['public'])
      $this->FilterFriends();

    if (is_string($func))
      return call_user_func_array($func, $arguments);

    if (is_callable($func))
      return call_user_func_array($func, $arguments);

    throw new Exception("phony_extend unrecognizable method type {$name}");
  }

  protected function add_variable($name, $is_public, $params = [])
  {
    if (!empty($this->variables[$name]))
      throw new Exception("Variable already exists. Refuse to override");

    if (!is_array($params))
      throw new Exception("Variable params should be array");

    $params['public'] = $is_public;

    $this->variables[$name] = $params;
  }

  public function __set($name, $value)
  {
    $var = &$this->variable($name);

    if (!$var['public'])
      $this->FilterFriends();

    if (!empty($var['set']))
    {
      $args = [$value, &$var['value']];
      if (!$this->ExecuteHooks($var['set'], $args))
        throw new Exception("Write in $name denied");
    }

    return $var['value'];
  }

  public function __get($name)
  {
    $var = &$this->variable($name);

    if (!$var['public'])
      $this->FilterFriends();

    if (!empty($var['get']))
      if (!$this->ExecuteHooks($var['get'], [$var['value']]))
        throw new Exception("Read from $name denied");

    if (!isset($var['value']))
      throw new Exception("Reading uninitialized value");

    return $var['value'];
  }

  private function &variable($name)
  {
    if (empty($this->variables[$name]))
      throw new Exception("Variable $name is undefined");
    return $this->variables[$name];
  }

  private function ExecuteHooks($hooks, &$arguments)
  {
    if (is_callable($hooks))
      $hooks = [$hooks];

    foreach ($hooks as $hook)
    {
      if (!is_callable($hook))
        throw new Exception("Hook is not callable!");

      $res = call_user_func_array($hook, $arguments);
      if (!$res)
        return false;
    }

    return true;
  }

  protected function FilterFriends()
  {
    $callstack = debug_backtrace();

    $stack_distance = 0;

    foreach ($callstack as $stackframe)
    {
      $stack_distance++;

      if (in_array($stackframe['function'],
          [
            'call_user_func_array',
            '__get',
            '__set',
            'phony\{closure}',
          ] ))
        continue;

      if (!isset($method)
        && $stack_distance > 1
        && strpos($stackframe['function'], '__Call') === false)
        $method = $stackframe['function'];

      if (isset($stackframe['class'])
        && in_array($stackframe['class'],
          [
            'phony\phony_implementation',
            'phony\phony_extend',
          ]))
        continue;

      break;
    }

    if (isset($stackframe['file'])
      && dirname($stackframe['file']) == __DIR__)
      return true;

    throw new Exception("Calling private phony::{$method} forbidden");
  }
}
