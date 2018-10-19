<?php namespace Tests;
/**
 * Copyright 2015 Openstack Foundation
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
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Redis;
use Laravel\BrowserKitTesting\TestCase as BaseTestCase;
/**
 * Class TestCase
 * @package Tests
 */
abstract class BrowserKitTestCase extends BaseTestCase
{
    use CreatesApplication;

    private $redis;

    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';

    public function setUp()
    {
        parent::setUp(); // Don't forget this!
        $this->redis = Redis::connection();
        $this->redis->flushall();
        $this->prepareForTests();
    }


    /**
     * Migrates the database and set the mailer to 'pretend'.
     * This will cause the tests to run quickly.
     *
     */
    protected function prepareForTests()
    {
        Artisan::call('migrate');
        //Mail::pretend(true);
        $this->seed('TestSeeder');
    }
}
