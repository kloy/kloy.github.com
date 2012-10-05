---
layout: post
title: "Yaml config files in Laravel"
date: 2012-04-11 08:14
comments: true
categories:
  - "Laravel"
  - "PHP"
---

One of the new features in laravel 3.1 is being able to listen for the the Config::loader event
allowing for more control over how config files are loaded. With a little bit of work we'll
be able to utilize this "hook" to load config files written in yaml as well as php.
<!--more-->

### Getting a yaml parser

Before we can use yaml files as configs, we need a way to parse yaml in php. The best
and easiest yaml parser I know of for PHP is symfony's. We can install symfony's yaml
component using composer. If you don't already have composer integrated into your laravel
app go ahead and checkout my article on
[Composer with laravel](http://www.keithloy.me/2012/04/composer-with-laravel/).

Now that you have composer integrated make sure your **composer.json** file looks like so...

  # /path/to/my/project/composer.json
  {
      "require": {
          "php": ">=5.3.0",
          "symfony/yaml": "2.0.*"
      }
  }

And run a composer install if you have not already run composer install, else run composer
update.

  cd /path/to/my/project
  php composer.phar install # only if not already installed
  php composer.phar update

You should now have the symfony yaml component.

### Register the yaml parser

Before we can use the parser, we need to register the parser in our IoC container.
We will do that now by adding the following code to the bottom of **start.php**.

  # in /path/to/my/project/application/start.php
  IoC::singleton('yaml_parser', function()
  {
      return new \Symfony\Component\Yaml\Parser();
  });

  IoC::singleton('yaml_dumper', function()
  {
      return new \Symfony\Component\Yaml\Dumper();
  });

What we just did was register the symfony yaml Dumper and Parser objects as singletons
in our IoC container. We can now get an instance of the yaml parser at any time by calling:

  $parser = IoC::resolve('yaml_parser');

### Listening to the load event

Time to hook into that load event mentioned in the intro. We will need to change the
default **Config::loader** event in **/path/to/project/application/start.php** to use
our **Config::file()** function instead of the default **Laravel\Config::file()**
function. We could technically implement an entirely new file loading process for config
files here, but we only wish to extend the default functionality to meet our needs.
And... Here's the code.

  # in /path/to/my/project/application/start.php
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

The require_once might have you scratching your head a bit if you are use to using
Laravel's autoloader. This is a work around as the autoloaders don't get initialized
until later in the start process and if we registered an autoloader for our myconfig.php
file it would actually get registered twice since the libraries dir get's registered as
well.

### MyConfig::file()

This is where we extend, or technically override, the default **Laravel\Config::file()**
function. On the with code!

  # in /path/to/my/project/application/libraries/myconfig.php
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

### Final notes

We can now use yaml files as config files. Nothing special is needed when calling Config::get(),
just use it as normal. For the astute programmer, you may notice that you can easily
add more config parsers in the if, if else block.