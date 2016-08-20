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
  }

  /*
    Driver callback:
      ($content, &$result)
      If driver returns false it treat like abort signal
   */
  public function RegisterDriver($device, $driver_name, $driver)
  {
    $driver_obj = $this->ConstructDriverObj($driver_name, $driver);

    if (empty($this->drivers[$device]))
      $this->drivers[$device] = [$driver_obj];
    else
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
    foreach ($this->drivers['source'] as $driver)
    {
      try
      {
        $result = $driver['func']($content, $new_version);
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
