<?php namespace phony;

require_once('phony_extend.php');

class phony_implementation extends phony_extend
{
  private $rewrite;
  private $extend;

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
    if (!method_exists($this, $name))
    {
      $this->FilterFriends();
      return call_user_func_array([$this, $name], $arguments);
    }

    if (empty($this->extend))
      $this->extend = new phony_extend();

    return call_user_func_array([$this->extend, $name], $arguments);
  }
}
