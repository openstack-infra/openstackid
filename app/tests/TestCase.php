<?php

class TestCase extends Illuminate\Foundation\Testing\TestCase {

    private $redis;

    public function setUp()
    {
        parent::setUp(); // Don't forget this!
        $this->redis = \RedisLV4::connection();
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

		return require __DIR__.'/../../bootstrap/start.php';
	}

}
