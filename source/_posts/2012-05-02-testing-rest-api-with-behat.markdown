---
layout: post
title: "Testing REST API with Behat"
date: 2012-05-02 08:17
comments: true
categories:
---

Today I'll cover testing REST APIs with [Behat](http://behat.org/). I will be using
[Laravel](http://laravel.com/) to build our sample REST API. Also, I will be using guzzle
as the REST client. This proves to be a much simpler method of testing the REST API then
writing a driver or extending mink.

This is more of an advanced tutorial so I'm going to skip over some of the more basic
setup steps or just briefly mention them. If you need additional setup help just ask in
the comments.

An example containing all of the code needed can be found on github at
[behat-rest-testing](https://github.com/kloy/behat-rest-testing).
<!--more-->

### Getting Started

Before we start we need to git a fresh copy of [Laravel](http://laravel.com/).

	# Clone laravel repo
	git://github.com/laravel/laravel.git

You will also need to setup a vhost with a local url of **foo.local** that points to the **public**
dir of the laravel project.

Now that we have a fresh project go ahead and cd into it and grab composer.

	# Install composer
	curl -s http://getcomposer.org/installer | php

Awesome, now we are ready to setup our **composer.json** with the necessary
dependencies. Go ahead and create **composer.json** at the top of our project and add the
following to it.

	// composer.json
	{
	    "require": {
	        "behat/behat": "2.3.*",
	        "guzzle/guzzle": "2.4.*"
	    },
	    "config": {
	        "bin-dir": "bin/"
	    }
	}

Now that we have our dependencies listed go ahead and install them.

	# install composer
	php composer.phar install

You should now have a few libraries in **vendor/** and an executable for **behat** in
**bin/**. We can now go ahead and initialize behat and get to working on our custom REST
Context.

	# initialize behat
	bin/behat --init

### Writing our REST Context

Create a file in **features/bootstrap/** called **RestContext.php**. Add the following to
**RestContext.php**...

	// features/bootstrap/RestContext.php
	<?php
	use Behat\Behat\Context\BehatContext;
	use Symfony\Component\Yaml\Yaml;

	/**
	 * Rest context.
	 */
	class RestContext extends BehatContext
	{

	    private $_restObject        = null;
	    private $_restObjectType    = null;
	    private $_restObjectMethod  = 'get';
	    private $_client            = null;
	    private $_response          = null;
	    private $_requestUrl        = null;

	    private $_parameters			= array();

	    /**
	     * Initializes context.
	     * Every scenario gets it's own context object.
	     */
	    public function __construct(array $parameters)
	    {
	        // Initialize your context here

	        $this->_restObject  = new stdClass();
	        $this->_client      = new Guzzle\Service\Client();
	    	$this->_parameters = $parameters;
	    }

	    public function getParameter($name)
	    {
	    	if (count($this->_parameters) === 0) {


	            throw new \Exception('Parameters not loaded!');
	    	} else {

	            $parameters = $this->_parameters;
	            return (isset($parameters[$name])) ? $parameters[$name] : null;
	    	}
	    }

	     /**
	     * @Given /^that I want to make a new "([^"]*)"$/
	     */
	    public function thatIWantToMakeANew($objectType)
	    {
	        $this->_restObjectType   = ucwords(strtolower($objectType));
	        $this->_restObjectMethod = 'post';
	    }

	     /**
	     * @Given /^that I want to find a "([^"]*)"$/
	     */
	    public function thatIWantToFindA($objectType)
	    {
	        $this->_restObjectType   = ucwords(strtolower($objectType));
	        $this->_restObjectMethod = 'get';
	    }

	    /**
	     * @Given /^that I want to delete a "([^"]*)"$/
	     */
	    public function thatIWantToDeleteA($objectType)
	    {
	        $this->_restObjectType   = ucwords(strtolower($objectType));
	        $this->_restObjectMethod = 'delete';
	    }

	    /**
	     * @Given /^that its "([^"]*)" is "([^"]*)"$/
	     */
	    public function thatTheItsIs($propertyName, $propertyValue)
	    {
	        $this->_restObject->$propertyName = $propertyValue;
	    }

	    /**
	     * @When /^I request "([^"]*)"$/
	     */
	    public function iRequest($pageUrl)
	    {
	        $baseUrl 			= $this->getParameter('base_url');
	        $this->_requestUrl 	= $baseUrl.$pageUrl;

	        switch (strtoupper($this->_restObjectMethod)) {
	            case 'GET':
	                $response = $this->_client
	                    ->get($this->_requestUrl.'?'.http_build_str((array)$this->_restObject))
	                    ->send();
	                break;
	            case 'POST':
	                $postFields = (array)$this->_restObject;
	                $response = $this->_client
	                    ->post($this->_requestUrl,null,$postFields)
	                    ->send();
	                break;
	            case 'DELETE':
	            	$response = $this->_client
	                    ->delete($this->_requestUrl.'?'.http_build_str((array)$this->_restObject))
	                    ->send();
	            	break;
	        }
	        $this->_response = $response;
	    }

	    /**
	     * @Then /^the response is JSON$/
	     */
	    public function theResponseIsJson()
	    {
	        $data = json_decode($this->_response->getBody(true));

	        if (empty($data)) {
	            throw new Exception("Response was not JSON\n" . $this->_response);
	        }
	    }

	    /**
	     * @Given /^the response has a "([^"]*)" property$/
	     */
	    public function theResponseHasAProperty($propertyName)
	    {
	        $data = json_decode($this->_response->getBody(true));

	        if (!empty($data)) {
	            if (!isset($data->$propertyName)) {
	                throw new Exception("Property '".$propertyName."' is not set!\n");
	            }
	        } else {
	            throw new Exception("Response was not JSON\n" . $this->_response->getBody(true));
	        }
	    }

	    /**
	     * @Then /^the "([^"]*)" property equals "([^"]*)"$/
	     */
	    public function thePropertyEquals($propertyName, $propertyValue)
	    {
	        $data = json_decode($this->_response->getBody(true));

	        if (!empty($data)) {
	        	if (!isset($data->$propertyName)) {
	                throw new Exception("Property '".$propertyName."' is not set!\n");
	            }
	            if ($data->$propertyName !== $propertyValue) {
	            	throw new \Exception('Property value mismatch! (given: '.$propertyValue.', match: '.$data->$propertyName.')');
	            }
	        } else {
	            throw new Exception("Response was not JSON\n" . $this->_response->getBody(true));
	        }
	    }

	    /**
	     * @Given /^the type of the "([^"]*)" property is ([^"]*)$/
	     */
	    public function theTypeOfThePropertyIsNumeric($propertyName,$typeString)
	    {
	        $data = json_decode($this->_response->getBody(true));

	        if (!empty($data)) {
	            if (!isset($data->$propertyName)) {
	                throw new Exception("Property '".$propertyName."' is not set!\n");
	            }
	            // check our type
	            switch (strtolower($typeString)) {
	                case 'numeric':
	                    if (!is_numeric($data->$propertyName)) {
	                        throw new Exception("Property '".$propertyName."' is not of the correct type: ".$theTypeOfThePropertyIsNumeric."!\n");
	                    }
	                    break;
	            }

	        } else {
	            throw new Exception("Response was not JSON\n" . $this->_response->getBody(true));
	        }
	    }

	    /**
	     * @Then /^the response status code should be (\d+)$/
	     */
	    public function theResponseStatusCodeShouldBe($httpStatus)
	    {
	        if ((string)$this->_response->getStatusCode() !== $httpStatus) {
	        	throw new \Exception('HTTP code does not match '.$httpStatus.
	        		' (actual: '.$this->_response->getStatusCode().')');
	        }
	    }

	     /**
	     * @Then /^echo last response$/
	     */
	    public function echoLastResponse()
	    {
	        $this->printDebug(
	            $this->_requestUrl."\n\n".
	            $this->_response
	        );
	    }
	}


So that was a bit of code. I'll sort of explain it in a moment after we get it working. In
order for these steps to work we need to add **RestContext** as a sub context to **FeatureContext**.
Let's do that.

	// features/bootstrap/FeatureContext.php
	<?php

	use Behat\Behat\Context\ClosuredContextInterface,
	    Behat\Behat\Context\TranslatedContextInterface,
	    Behat\Behat\Context\BehatContext,
	    Behat\Behat\Exception\PendingException;
	use Behat\Gherkin\Node\PyStringNode,
	    Behat\Gherkin\Node\TableNode;

	//
	// Require 3rd-party libraries here:
	//
	//   require_once 'PHPUnit/Autoload.php';
	//   require_once 'PHPUnit/Framework/Assert/Functions.php';
	//
	require_once 'RestContext.php';

	/**
	 * Features context.
	 */
	class FeatureContext extends BehatContext
	{
	    /**
	     * Initializes context.
	     * Every scenario gets it's own context object.
	     *
	     * @param   array   $parameters     context parameters (set them up through behat.yml)
	     */
	    public function __construct(array $parameters)
	    {
	        // Initialize your context here
	        $this->useContext('RestContext', new RestContext($parameters));
	    }
	}

So what we did is require our RestContext.php file and instantiate it in
*$this->useContext('RestContext', new RestContext($parameters));*. Now when you type
**bin/behat -dl** you should see a list commands that look like...

	# output from cli
	Given /^that I want to make a new "([^"]*)"$/
	Given /^that I want to find a "([^"]*)"$/
	Given /^that I want to delete a "([^"]*)"$/
	Given /^that its "([^"]*)" is "([^"]*)"$/
	When /^I request "([^"]*)"$/
	Then /^the response is JSON$/
	Given /^the response has a "([^"]*)" property$/
	Then /^the "([^"]*)" property equals "([^"]*)"$/
	Given /^the type of the "([^"]*)" property is ([^"]*)$/
	Then /^the response status code should be (\d+)$/
	Then /^echo last response$/

All of these steps are coming from our RestContext class.

### Adding behat.yml

We need a config file for behat specifying where our api is. Create the file **behat.yml**
at the top dir of your project.

	# behat.yml
	default:
	    context:
	        parameters:
	            base_url: http://foo.local

The property **base_url** is where we specified the location of our api.

### Writing our features

Now that all the ground work is in place, let's write a few features for testing. Go ahead
and create a file **features/user.feature** and add the following to it.

	# features/user.feature
	Feature: Testing the RESTfulness of the Index controller
    Let's see how RESTish this is

    Scenario: Creating a new User
        Given that I want to make a new "User"
        And that its "name" is "Chris"
        When I request "/user"
        Then the response is JSON
        And the response has a "userId" property
        And the type of the "userId" property is numeric
        Then the response status code should be 200

    Scenario: Finding a User
        Given that I want to find a "User"
        And that its "name" is "Chris"
        When I request "/user"
        Then the "name" property equals "Chris"

    Scenario: Deleting a User
        Given that I want to delete a "User"
        And that its "name" is "Chris"
        When I request "/user"
        Then the "status" property equals "true"

Now that we have our feature let's run it with **bin/behat features/user.feature**. You should
get errors with a large amount of html spit back out at you. This is because Laravel
does not have a route/api for what is being tested. We will create that in the next step.

### Creating our API

Our API is for demonstration purposes only, so I'm going to go ahead and provide a quick
stub API that will pass our expectations. Enter the following code into **application/routes.php**.

	// in application/routes.php
	Route::post('user', function()
	{
		$data = array('userId' => 1);
		return Response::make(json_encode($data), 200,
			array('Content-Type' => 'application/json'));
	});

	Route::get('user', function()
	{
		$data = array('name' => 'Chris');
		return Response::make(json_encode($data), 200,
			array('Content-Type' => 'application/json'));
	});

	Route::delete('user', function()
	{
		$data = array('status' => "true");
		return Response::make(json_encode($data), 200,
			array('Content-Type' => 'application/json'));
	});

Go ahead and run **bin/behat features/user.feature** and you should get all green with a
message like so...

	Feature: Testing the RESTfulness of the Index controller
    Let's see how RESTish this is

	  Scenario: Creating a new User                      # features/user.feature:4
	    Given that I want to make a new "User"           # RestContext::thatIWantToMakeANew()
	    And that its "name" is "Chris"                   # RestContext::thatTheItsIs()
	    When I request "/user"                           # RestContext::iRequest()
	    Then the response is JSON                        # RestContext::theResponseIsJson()
	    And the response has a "userId" property         # RestContext::theResponseHasAProperty()
	    And the type of the "userId" property is numeric # RestContext::theTypeOfThePropertyIsNumeric()
	    Then the response status code should be 200      # RestContext::theResponseStatusCodeShouldBe()

	  Scenario: Finding a User                           # features/user.feature:13
	    Given that I want to find a "User"               # RestContext::thatIWantToFindA()
	    And that its "name" is "Chris"                   # RestContext::thatTheItsIs()
	    When I request "/user"                           # RestContext::iRequest()
	    Then the "name" property equals "Chris"          # RestContext::thePropertyEquals()

	  Scenario: Deleting a User                          # features/user.feature:19
	    Given that I want to delete a "User"             # RestContext::thatIWantToDeleteA()
	    And that its "name" is "Chris"                   # RestContext::thatTheItsIs()
	    When I request "/user"                           # RestContext::iRequest()
	    Then the "status" property equals "true"         # RestContext::thePropertyEquals()

	3 scenarios (3 passed)
	15 steps (15 passed)
	0m0.131s

### Closing notes

Now that we have a REST API in place and a Rest Context for behat you should be able to
see how we could test any REST API. If you need more steps to use when testing your REST
APIs just add them to the RestContext class and they will then be available to use in your
Gherkin features.

### Credits

This article was heavily inspired by
[behat + fuelphp = restful testing happiness](http://blog.phpdeveloper.org/?p=456) written
by Chris Cornutt. The RestContext class is just a refactor of his FeatureContextRest class
to allow using it as a sub context which I feel makes it more natural to use and easier to
reuse in projects. I also wanted to make a point that principals apply to any REST API and
not just one built with FuelPHP.