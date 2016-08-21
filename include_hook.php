<?php namespace phony;

require_once('exception.php');

phony::add_variable ('included_files', false,
[
  'type' => 'variable',
  'value' => [],
  'set' => function($new, &$old)
  {
    $old[] = $new;
    return true;
  }
]);

function register_included_file($filename)
{
  if (!is_file_included($filename))
    phony()->included_files = $filename;
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
  var_dump("INCLUDING $filename");

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
  $result = phony::__include($filename);

  if (!$result)
    throw Exception("Unable to require {$filename}");
});

phony::add('require_once', true, function($filename)
{
  $result = phony::__include_once($filename);

  if (!$result)
    throw Exception("Unable to require {$filename}");
});

phony::GetRewriteObj()->RegisterDriver('source.php', 'include',
  function ($filename, $content, &$new)
  {
    $dictionary =
    [
      'include' => 'phony::__include',
      'include_once' => 'phony::__include_once',
      'require' => 'phony::__require',
      'require_once' => 'phony::__require_once',
    ];

    $new = str_replace(
      array_keys($dictionary)
      , array_values($dictionary)
      , $content);

    return true;
  });

phony::GetRewriteObj()->RegisterDriver('source.phony', 'include',
  function ($filename, $content, &$new)
  {
    // TODO: Unlike php we shouldn't treat it as keywords
    // Replace only when it outside class mention

    $dictionary =
    [
      'include' => 'phony::__include',
      'include_once' => 'phony::__include_once',
      'require' => 'phony::__require',
      'require_once' => 'phony::__require_once',
    ];

    $new = str_replace(
      array_keys($dictionary)
      , array_values($dictionary)
      , $content);

    return true;

    return true;
  });
