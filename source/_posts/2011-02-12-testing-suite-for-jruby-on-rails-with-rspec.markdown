---
layout: post
title: "Testing Suite for jRuby on Rails with RSpec"
date: 2011-02-12 21:08
comments: true
categories:
  - "Ruby on Rails"
---

It took me some time to figure out how to setup a performant testing environment in Rails 3 when using
jRuby in my development environment. Therefore, I bring you a tutorial on how to setup my testing
environment to hopefully save you time if you must also go down this path. I have chosen to use the following
tools in my testing environment: Rspec for BDD, Spork to decrease loading time of Rails and decrease the time
it takes to run RSpec tests, ZenTest for auto-testing, and Nailgun to decrease the time to load the JVM.
<!--more-->

### A few things to note

A few things to note before starting this tutorial are as follows. Whenever you see the character "$" this denotes
a terminal command. Type the command following the "$" into terminal to execute the command. Also, my text editor
of choice is Textmate, so if you see "mate" in a command replace it with whatever your editor of choice is. One last
note before we start. I use "..." or the vertical form of "..." to represent additional code pre-existing above.

This tutorial assumes you are using jruby with RVM.
If you are not using RVM for your jruby installation
you need to use "jruby -S" instead of "ruby" when
executing commands as well as prefix "gem install"
with "jruby -S". For example, "jruby -S gem install
ZenTest".

I highly recommend using RVM. If you would like
information on how to install RVM, as well as using
RVM to install jruby checkout one of my previous posts
[Install jRuby on Rails with RVM](http://www.keithloy.me/2011/02/install-jruby-on-rails-with-rvm/)

### Step 1 :: Install RSpec

RSpec is a Behaviour-Driven Development tool for Ruby programmers.
RSpec provides clean and readable self documenting tests.


*Create app without tests*
`$ rails new demo -T`

*Edit Gemfile*
`$ mate Gemfile`

    .
    .
    .
    group :development do
    	gem 'rspec-rails', '2.5.0'
    end
    group :test do
    	gem 'rspec', '2.5.0'
    end

*Install rspec for your current Rails project*
`$ rails generate rspec:install`

### Step 2 :: Install Spork

Spork will greatly decrease the amount of time it takes to run
your rspec tests. Unfortunately it does not work with jruby
by default so we will need to grab a special branch of
the gem.

*Grab jruby version of Spork*
`$ git clone git://github.com/rdp/spork.git`
`$ cd spork`
`$ gem build spork.gemspec`
`$ gem install spork-0.8.3`

Now we want to have rspec utilize spork whenever running RSpec tests.

*Edit .rspec*
`$ mate .rspec`

*Add the following line to .rspec*
`--drb`

Bootstrapping spork will modify RSpec's helpers to use Spork
in the current Rails project when RSpec is ran.

*Bootstrap spork*
`$ spork --bootstrap`

### Spork hacks for Rails 3

Spork will not work with Rails 3, so we need to edit a couple
of files in order for it to work.

*Edit spec/spec_helper.rb*

    Spork.prefork do
      # Loading more in this block will cause your tests to run faster. However,
      # if you change any configuration or code from libraries loaded here, you'll
      # need to restart spork for it take effect.
      ENV["RAILS_ENV"] ||= 'test'
      unless defined?(Rails)
        require File.dirname(__FILE__) + "/../config/environment"
      end
      require 'rspec/rails'

      # Requires supporting files with custom matchers and macros, etc,
      # in ./support/ and its subdirectories.
      Dir["#{File.dirname(__FILE__)}/support/**/*.rb"].each {|f| require f}

      Rspec.configure do |config|
        # == Mock Framework
        #
        # If you prefer to use mocha, flexmock or RR, uncomment the appropriate line:
        #
        # config.mock_with :mocha
        # config.mock_with :flexmock
        # config.mock_with :rr
        config.mock_with :rspec

        config.fixture_path = "#{::Rails.root}/spec/fixtures"

        # If you're not using ActiveRecord, or you'd prefer not to run each of your
        # examples within a transaction, comment the following line or assign false
        # instead of true.
        config.use_transactional_fixtures = true

        ### Part of a Spork hack. See http://bit.ly/arY19y
        # Emulate initializer set_clear_dependencies_hook in
        # railties/lib/rails/application/bootstrap.rb
        ActiveSupport::Dependencies.clear
      end
    end

*Edit config/application.rb*

    class Application < Rails::Application
      .
      .
      .
    	### Part of a Spork hack. See http://bit.ly/arY19y
    	if Rails.env.test?
    	  initializer :after => :initialize_dependency_mechanism do
    			# Work around initializer in railties/lib/rails/application/bootstrap.rb
    			ActiveSupport::Dependencies.mechanism = :load
    		end
    	end
    end

### Step 3 :: Install ZenTest for auto-testing

ZenTest is a gem that will automatically run your test suite when a file change occurs.

*Run the following command to install ZenTest*
`$ gem install ZenTest`

To start autotest you must run it from the the root of your Rails project of your dir.

*Start autotest*
`$ autotest`

### Step 4 :: Bonus: Startup Nailgun

**This step is not necessary, but has allowed me to run my tests in under two seconds
on my machine.**

Nailgun is a server that keeps a JVM open. This minimizes the time our test suite takes
to run as the JVM is not required to load whenever we run our tests.

*Start the Nailgun server*
`$ ruby --ng-server &`

Now that the Nailgun server is running navigate to your Rails project's dir in terminal.

*Run RSpec tests using the Nailgun Server*
`$ ruby --ng rspec spec/`

Your tests should now be running significantly faster with the help of nailgun and spork!
