<?php namespace phony;

require_once('exception.php');

phony::add('included_files', false,
[
  'type' => 'variable',
  'init' => [],
]);

function register_included_file($filename)
{
  if (!is_file_included($filename))
    phony()->included_files[] = $filename;
}

function is_file_included($filename)
{
  if (in_array($filename, phony()->included_files))
    return true;

  $included_files = get_included_files();

  return in_array($filename, $included_files);
}

function phony_include($filename, $before_include)
{
  // We hook only .phony extensions
  if (strrpos($filename, ".phony") === false)
    return include($filename);

  $include_path = get_include_path();
  $include_folders = explode(":", $include_path);

  foreach ($include_folders as $folder)
  {
    $file = $folder."/".$filename;

    if (!file_exists($file))
      continue;

    // TODO: Understand absolute and local path

    if (!$before_include($file, $filename))
      return true;

    register_included_file($file);

    $content = phony::Rewrite($file);

    eval($content);
    return true;
  }

  return false;
}

phony::add('include', true, function ($filename)
{
  return phony_include($filename, function ($file, $filename)
  {
    return !is_file_included($file);
  });
});

phony::add('include_once', true, function ($filename)
{
  return phony_include($filename, function ($file, $filename)
  {
    return !is_file_included($file);
  });
});

phony::add('require', true, function ($filename)
{
  $result = phony::include($filename);

  if (!$result)
    throw Exception("Unable to require {$filename}");
});

phony::add('require_once', true, function($filename)
{
  $result = phony::include_once($filename);

  if (!$result)
    throw Exception("Unable to require {$filename}");
});
