---
layout: post
title: "Install jRuby on Rails with RVM"
date: 2011-02-05 20:57
comments: true
categories:
  - "Ruby on Rails"
---

Recently I was asked to switch to using Rails for a new project at my job. At my company we have
had nightmares deploying our PHP applications on multiple customers environments, therefore I was
asked to use jRuby instead of Ruby so that we may deploy with the JVM. In my research I came
across [RVM](http://rvm.beginrescueend.com/) (Ruby Version Manager), a great app
for managing multiple version of Ruby and/or jRuby on a single machine. In the following
tutorial I will demonstrate how to install RVM and then use it to install jRuby and create
a gem set. Finally we will install Rails and launch a development server to demonstrate it
working.
<!--more-->

### A few things to know

When going through this tutorial, the following information is important. Whenever you see
the symbol "$" this denotes the following text is a command to type in terminal. My text
editor of choice is Textmate, so in my commands you will see "mate" sometimes. Feel free to
replace "mate" with "nano", "vim", or which ever your editor of choice might be. Also, I am
on OS X, so if you are using another Unix OS, replace my instructions to edit ".profile"
with ".bashrc" or equivalent.

### Step 1 :: Install RVM (Ruby Version Manager)

*Install RVM*
`$ bash < <( curl http://rvm.beginrescueend.com/releases/rvm-install-head )`

*Open .profile*
`$ mate ~/.profile`

*Insert the following function at the bottom of the .profile file and save*

    # This loads RVM into a shell session.
    [[ -s "$HOME/.rvm/scripts/rvm" ]] && . "$HOME/.rvm/scripts/rvm"

*Now verify RVM is properly installed*
`$ type rvm | head -1`

*You should see the following printed in terminal*
`rvm is a function`

### Step 2 :: Install jRuby

*Install jRuby with RVM*
`$ rvm install jruby-1.5.6`
`$ rvm jruby-1.5.6`

*Check ruby version in terminal*
`$ ruby -v`

### Step 3 :: Create and set default gem set

A gem set is a collection of gems you may have for a particular project. The gem set is associated with the current version of
Ruby being used by RVM. Gem sets are useful for having complete control over which versions of gems you use for each project.

*Create a gemset*
`$ rvm --create use jruby-1.5.6@example`

*Set new gemset to the default RVM uses*
`$ rvm --default use jruby-1.5.6@example`

### Step 4 :: Install Rails

**Note DO NOT RUN GEM COMMANDS AS SUDO.** Sudo is different user and circumnavigates where gems are stored relative to where RVM places them.

*Install Rails*
`$ gem install rails --version 3.0.3`

### Step 5 :: Create Rails project

*Create new Rails project and switch to it*
`$ rails new demo`
`$ cd demo`

### Step 6 :: Modify Gemfile

*Edit the Gemfile*
`$ mate Gemfile`

*Replace the contents of Gemfile with the following.*

    source 'http://rubygems.org'

    gem 'rails', '3.0.3'

    if defined?(JRUBY_VERSION)
      gem 'jdbc-sqlite3', '3.6.14.2.056'
      gem 'activerecord-jdbc-adapter', '1.1.1'
      gem 'activerecord-jdbcsqlite3-adapter', '1.1.1'
      gem 'jruby-openssl', '0.7.3'
    else
      gem 'sqlite3-ruby', :require => 'sqlite3'
    end

### Step 7 :: Install gems with bundle

*Install all of the gems in your Gemfile*
`$ bundle install`

### Step 8 :: Startup WEBrick and checkout your app!

*Start a rails development server*
`$ rails server`

Load a browser and navigate to http://localhost:3000 to checkout your dummy app.
