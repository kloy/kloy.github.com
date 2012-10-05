---
layout: post
title: "Composer with Laravel"
date: 2012-04-10 05:23
comments: true
categories:
  - "Laravel"
  - "PHP"
---

Something I needed in my projects was the ability to seemlessly use
[composer](http://getcomposer.org/) to manage some of my packages as well as third party
ones such as the symfony components. With a little digging and a bit of work, I achieved
this goal. In this article I'll walk you through the process.
<!--more-->

### Let's do this

Firstly, lets install composer.

	# navigate to your project
	$ cd /path/to/my/project
	# install composer
	$ curl -s http://getcomposer.org/installer | php

Now create a **composer.json** file in the top path of your project. Here is my
**composer.json** file for reference.

	# in /path/to/my/project/composer.json
	{
	    "require": {
	        "php": ">=5.3.0",
	        "kloy/cli": "0.0.*",
	        "vendor/package": "version",
	        ...
	    }
	}

Since we have a **composer.json** file, we can now install our composer packages.

	# Install composer packages
	$ php composer.phar install

All your packages should now be installed in **/path/to/my/project/vendors**. We need to
setup laravel to use the composer autoloader now.

Modify your **paths.php** file to have a path for where composer installs it's packages.

	# in /path/to/my/project/paths.php
	// --------------------------------------------------------------
	// The path to the composer vendors directory.
	// --------------------------------------------------------------
	$paths['composer'] = 'vendor';

Last thing to do is make sure **index.php** is requiring the composer packages using the
composer autoloader. Add the following code to the **index.php** script.

	# in /path/to/my/project/public/index.php
	// --------------------------------------------------------------
	// Set the core Laravel path constants.
	// --------------------------------------------------------------
	require '../paths.php';

	// --------------------------------------------------------------
	// Autoload composer vendors.
	// --------------------------------------------------------------
	require path('composer').DS.'.composer'.DS.'autoload.php';

If you want the composer autoloader to work in artisan as well, just add the same require
from above to the artisan script. You could put the require statement for composer's
autolaoder in start.php, but this would need to be done for all bundles as well.

Congratualtions! You will now be able to use your composer packages in your application
and bundles.

