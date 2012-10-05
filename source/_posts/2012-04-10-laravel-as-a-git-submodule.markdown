---
layout: post
title: "Laravel as a git submodule"
date: 2012-04-10 08:05
comments: true
categories:
  - "Laravel"
  - "PHP"
---

Laravel is a very active framework with minor patches every few days it seems. It is nice
to be able to easily integrate these patches, as they usually contain bug fixes, into our
applications easily. I'll present the method I use for achieving this which utilizes
[git submodules](http://book.git-scm.com/5_submodules.html).
<!--more-->

### Get laravel

First let's go ahead and download [laravel](http://laravel.com/download). I'm assuming
you know how to install the default laravel app on your own server.

### Remove the framework

Now that we have laravel we are going to do something that seems kind of awkward, delete
the framework. We are doing this as we will be including the framework as a git
submodule shortly.

	$ cd /path/to/my/app
	$ rm -rf laravel

You can of course just use your file browser as well :-D

### Setting up git

I'll assume this is a brand new project where we need to go ahead and setup a git repo.
Let's do that.

	$ cd /path/to/my/app
	$ git init

Ok, we have our git repo, now let's include laravel as a git submodule.

	$ cd /path/to/my/app
	$ git submodule add https://github.com/laravel/laravel

You should now have a laravel dir in your app. The framework is actually nested in
**laravel/laravel**.

### Correcting the sys path

Currently **path('sys')** expects laravel to be in **/path/to/my/app/laravel** but ours
is in **/path/to/my/app/laravel/laravel**. Let's fix that. Open **/path/to/my/app/paths.php**
and modify it too look like so.

	// /path/to/my/app/paths.php
	// --------------------------------------------------------------
	// The path to the Laravel directory.
	// --------------------------------------------------------------
	$paths['sys'] = 'laravel/laravel';

Laravel is now included in your app as a git submodule and will work as any other git
submodule would! I personally fork laravel and use my forked repo instead of using laravel's
repo to allow for easily contributing back to laravel via pull requests on github. I'd
advocate this method if you are familiar with forking.

