<?php namespace phony;

class Exception extends \Exception
{
}

class Rethrow extends Exception
{
  private $original_exception;

  public function __construct($e, $message)
  {
    $this->original_exception = $e;

    parent::__construct($message);
  }

  public function getOriginMessage()
  {
    return $this->original_exception->getMessage();
  }

  public function __call($name, $arguments)
  {
    return call_user_method_array([$this->original_exception, $name], $arguments);
  }
}
