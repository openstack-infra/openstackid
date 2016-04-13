<?php
use Illuminate\Support\Facades\Redis;

/**
 * Class TestCase
 */
class TestCase extends Illuminate\Foundation\Testing\TestCase
{
    private $redis;

    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }

    public function __construct(){

    }

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
