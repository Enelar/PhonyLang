<?php namespace phony;

require_once('exception.php');

function phony_include($filename)
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

    $content = phony::Rewrite($file);

    eval($content);
  }
}
