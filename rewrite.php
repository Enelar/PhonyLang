<?php namespace phony;

require_once('exception.php');

class rewrite
{
  private $drivers = [];
  private $devices = [];

  public function RegisterDevice($device)
  {
    if (in_array($device, $this->devices))
      throw Exception("Device $device already registered");

    $this->devices[] = $device;
    $this->drivers[$device] = [];
  }

  /*
    Driver callback:
      ($scope, $target, &$result)
      If driver returns false it treat like abort signal
   */
  public function RegisterDriver($device, $driver_name, $driver)
  {
    $driver_obj = $this->ConstructDriverObj($driver_name, $driver);

    if (!in_array($device, $this->devices))
      throw new Exception("Device is unsupported");

    $this->drivers[$device][] = $driver_obj;
  }

  private function ConstructDriverObj($driver_name, $driver)
  {
    return
    [
      'name' => $driver_name,
      'func' => $driver,
    ];
  }

  public function Tranverse($filename)
  {
    $content = file_get_contents($filename);

    // only whole document rewrite currently supported
    $content = $this->RunDrivers('source', $filename, $content);
    if (strrpos($filename, '.php'))
      $content = $this->RunDrivers('source.php', $filename, $content);
    if (strrpos($filename, '.phony'))
      $content = $this->RunDrivers('source.phony', $filename, $content);

    return $content;
  }

  private function RunDrivers($device, $scope, $content)
  {
    foreach ($this->drivers[$device] as $driver)
    {
      try
      {
        $result = $driver['func']($scope, $content, $new_version);
      } catch (Exception $e)
      {
        throw Rethrow("Driver {$driver['name']} throw an exception");
      }

      if (!$result)
        throw Exception("Driver {$driver['name']} reports issue");

      $content = $new_version;
    }

    return $content;
  }

}


phony::add_variable('rewrite', false,
[
  'value' => new rewrite(),
]);

phony::add('Rewrite', true, function ($file)
{
  $rewrite = phony::GetRewriteObj();

  return $rewrite->Tranverse($file);
});

phony::add('GetRewriteObj', false, function &()
{
  // TODO: Make reference object

  $ret = &phony()->rewrite;
  return $ret;
});

phony::GetRewriteObj()->RegisterDevice('source');
phony::GetRewriteObj()->RegisterDevice('source.php');
phony::GetRewriteObj()->RegisterDevice('source.phony');
