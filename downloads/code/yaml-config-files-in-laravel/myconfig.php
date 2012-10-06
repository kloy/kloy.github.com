<?php

class MyConfig extends Laravel\Config
{
  /**
   * Load the configuration items from a configuration file.
   *
   * @param  string  $bundle
   * @param  string  $file
   * @return array
   */
  public static function file($bundle, $file)
  {
    $config = array();

    // Configuration files cascade. Typically, the bundle configuration array is
    // loaded first, followed by the environment array, providing the convenient
    // cascading of configuration options across environments.
    foreach (static::paths($bundle) as $directory)
    {
      $semi_path = $directory.$file;
      if ($directory !== '')
      {
        // Typical php configs are most common, so check first.
        if (file_exists($path = $semi_path.EXT))
        {
          $config = array_merge($config, require $path);
        }
        // Let's get a yaml config
        else if (file_exists($path = "$semi_path.yml"))
        {
          $parser = IoC::resolve('yaml_parser');
          $contents = file_get_contents($path);
          $config = array_merge($config, $parser->parse($contents));
        }
      }
    }

    return $config;
  }
}
