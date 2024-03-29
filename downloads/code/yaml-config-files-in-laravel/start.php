<?php
/*
|--------------------------------------------------------------------------
| Laravel Configuration Loader
|--------------------------------------------------------------------------
|
| The Laravel configuration loader is responsible for returning an array
| of configuration options for a given bundle and file. By default, we
| use the files provided with Laravel; however, you are free to use
| your own storage mechanism for configuration arrays.
|
*/

Laravel\Event::listen(Laravel\Config::loader, function($bundle, $file)
{
  require_once path('app').'libraries'.DS.'myconfig.php';
  return MyConfig::file($bundle, $file);
  // return Laravel\Config::file($bundle, $file);
});