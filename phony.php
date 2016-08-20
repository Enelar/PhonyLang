<?php namespace phony;

class phony_implementation
{
  private $rewrite;

  public function Rewrite($file)
  {
    $rewrite = $this->GetRewriteObj();

    return $rewrite->Tranverse($file);
  }

  private function &GetRewriteObj()
  {
    if (empty($this->rewrite))
      $this->rewrite = new rewrite();

    return $this->rewrite;
  }

  public function __Call($name, $arguments)
  {
    $this->FilterFriends();

    return call_user_method_array([$this, $name], $arguments);
  }

  private function FilterFriends()
  {
    debug_print_backtrace();
  }
}

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
