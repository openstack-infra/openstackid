<?php

/**
 * Copyright 2015 OpenStack Foundation
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 **/

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Illuminate\View\View;
use Illuminate\Auth\UserInterface;
use Illuminate\Foundation\Testing\Client;
use auth\User;
use Behat\MinkExtension\Context\MinkContext;

class LaravelContext
    extends MinkContext
    implements Context, SnippetAcceptingContext
{
    /**
     * The Illuminate application instance.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * The HttpKernel client instance.
     *
     * @var \Illuminate\Foundation\Testing\Client
     */
    protected $client;

    private $redis;

    protected $current_realm;

    public function __construct()
    {

    }

    /**
     * Migrates the database and set the mailer to 'pretend'.
     * This will cause the tests to run quickly.
     *
     */
    protected function prepareForTests()
    {
        Artisan::call('migrate');
        Mail::pretend(true);
        $this->seed('TestSeeder');
    }

    /**
     * Creates the application.
     *
     * @return Symfony\Component\HttpKernel\HttpKernelInterface
     */
    public function createApplication()
    {
        $unitTesting = true;

        $testEnvironment = 'testing';

        $app = require_once __DIR__ . '/../../../../bootstrap/start.php';
        return $app;
    }

    public function setUp()
    {
        if ( ! $this->app)
        {
            $this->refreshApplication();
        }

        $this->redis = \RedisLV4::connection();
        $this->redis->flushall();
        $this->prepareForTests();
    }

    /**
     * Refresh the application instance.
     *
     * @return void
     */
    protected function refreshApplication()
    {
        $this->app = $this->createApplication();

        $this->client = $this->createClient();

        $this->app->setRequestForConsoleEnvironment();

        $this->app->boot();
    }

    /**
     * Set the session to the given array.
     *
     * @param  array  $data
     * @return void
     */
    public function session(array $data)
    {
        $this->startSession();

        foreach ($data as $key => $value)
        {
            $this->app['session']->put($key, $value);
        }
    }

    /**
     * Flush all of the current session data.
     *
     * @return void
     */
    public function flushSession()
    {
        $this->startSession();

        $this->app['session']->flush();
    }

    /**
     * Start the session for the application.
     *
     * @return void
     */
    protected function startSession()
    {
        if ( ! $this->app['session']->isStarted())
        {
            $this->app['session']->start();
        }
    }

    /**
     * Set the currently logged in user for the application.
     *
     * @param  \Illuminate\Auth\UserInterface  $user
     * @param  string  $driver
     * @return void
     */
    public function be(UserInterface $user, $driver = null)
    {
        $this->app['auth']->driver($driver)->setUser($user);
    }

    /**
     * Seed a given database connection.
     *
     * @param  string  $class
     * @return void
     */
    public function seed($class = 'DatabaseSeeder')
    {
        $this->app['artisan']->call('db:seed', array('--class' => $class));
    }

    /**
     * Create a new HttpKernel client instance.
     *
     * @param  array  $server
     * @return \Symfony\Component\HttpKernel\Client
     */
    protected function createClient(array $server = array())
    {
        return new Client($this->app, $server);
    }


    /**
     * @Given Prepare For Tests is Done
     */
    public function prepareForTestsIsDone()
    {
        $this->setUp();

        if (Schema::hasTable('banned_ips'))
            DB::table('banned_ips')->delete();
        if (Schema::hasTable('user_exceptions_trail'))
            DB::table('user_exceptions_trail')->delete();
        if (Schema::hasTable('server_configuration'))
            DB::table('server_configuration')->delete();
        if (Schema::hasTable('server_extensions'))
            DB::table('server_extensions')->delete();
        if (Schema::hasTable('oauth2_client_api_scope'))
            DB::table('oauth2_client_api_scope')->delete();
        if (Schema::hasTable('oauth2_client_authorized_uri'))
            DB::table('oauth2_client_authorized_uri')->delete();
        if (Schema::hasTable('oauth2_access_token'))
            DB::table('oauth2_access_token')->delete();
        if (Schema::hasTable('oauth2_refresh_token'))
            DB::table('oauth2_refresh_token')->delete();
        if (Schema::hasTable('oauth2_client'))
            DB::table('oauth2_client')->delete();
        if (Schema::hasTable('openid_trusted_sites'))
            DB::table('openid_trusted_sites')->delete();
        if (Schema::hasTable('openid_associations'))
            DB::table('openid_associations')->delete();
        if (Schema::hasTable('user_actions'))
            DB::table('user_actions')->delete();
        if (Schema::hasTable('openid_users'))
            DB::table('openid_users')->delete();
        if (Schema::hasTable('oauth2_api_endpoint_api_scope'))
            DB::table('oauth2_api_endpoint_api_scope')->delete();
        if (Schema::hasTable('oauth2_api_endpoint'))
            DB::table('oauth2_api_endpoint')->delete();
        if (Schema::hasTable('oauth2_api_scope'))
            DB::table('oauth2_api_scope')->delete();
        if (Schema::hasTable('oauth2_api'))
            DB::table('oauth2_api')->delete();
        if (Schema::hasTable('oauth2_resource_server'))
            DB::table('oauth2_resource_server')->delete();

        $this->prepareForTests();
        $this->current_realm = Config::get('app.url');
    }

    /**
     * Call a controller action and return the Response.
     *
     * @param  string  $method
     * @param  string  $action
     * @param  array   $wildcards
     * @param  array   $parameters
     * @param  array   $files
     * @param  array   $server
     * @param  string  $content
     * @param  bool    $changeHistory
     * @return \Illuminate\Http\Response
     */
    public function action($method, $action, $wildcards = array(), $parameters = array(), $files = array(), $server = array(), $content = null, $changeHistory = true)
    {
        $uri = $this->app['url']->action($action, $wildcards, true);

        return $this->call($method, $uri, $parameters, $files, $server, $content, $changeHistory);
    }

    /**
     * Call the given URI and return the Response.
     *
     * @param  string  $method
     * @param  string  $uri
     * @param  array   $parameters
     * @param  array   $files
     * @param  array   $server
     * @param  string  $content
     * @param  bool    $changeHistory
     * @return \Illuminate\Http\Response
     */
    public function call()
    {
        call_user_func_array(array($this->client, 'request'), func_get_args());

        return $this->client->getResponse();
    }

    /**
     * Asserts that a condition is true.
     *
     * @param  boolean $condition
     * @param  string  $message
     * @throws PHPUnit_Framework_AssertionFailedError
     */
    public static function assertTrue($condition, $message = '')
    {
       if(!$condition)
           throw new \LogicException($message);
    }


}