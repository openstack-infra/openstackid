<?php

/*
|--------------------------------------------------------------------------
| Register The Laravel Class Loader
|--------------------------------------------------------------------------
|
| In addition to using Composer, you may use the Laravel class loader to
| load your controllers and models. This is useful for keeping all of
| your classes in the "global" namespace without Composer updating.
|
*/
use openid\exceptions\InvalidOpenIdMessageException;
use utils\services\ServiceLocator;
use utils\services\UtilsServiceCatalog;
use oauth2\exceptions\InvalidOAuth2Request;
use Monolog\Logger;
use Monolog\Handler\NativeMailerHandler;
use Illuminate\Support\Facades\App;


ClassLoader::addDirectories(array(
    app_path() . '/commands',
    app_path() . '/controllers',
    app_path() . '/models',
    app_path() . '/database/seeds',
));

/*
|--------------------------------------------------------------------------
| Application Error Logger
|--------------------------------------------------------------------------
|
| Here we will configure the error logger setup for the application which
| is built on top of the wonderful Monolog library. By default we will
| build a rotating log file setup which creates a new file each day.
|
*/

$logFile = 'log-' . php_sapi_name() . '.txt';

Log::useDailyFiles(storage_path() . '/logs/' . $logFile,$days = 0, $level = 'debug');

//set email log
$to          = Config::get('log.to_email');
$from        = Config::get('log.from_email');
if(!empty($to) && !empty($from)){
    $subject     = 'openstackid error';
    $mono_log    = Log::getMonolog();
    $handler = new NativeMailerHandler($to, $subject, $from,$level = Logger::WARNING);
    $mono_log->pushHandler($handler);
}

if (Config::get('database.log', false)){

    Event::listen('illuminate.query', function($query, $bindings, $time, $name)
    {
        $data = compact('bindings', 'time', 'name');

        // Format binding data for sql insertion
        foreach ($bindings as $i => $binding)
        {
            if ($binding instanceof \DateTime)
            {
                $bindings[$i] = $binding->format('\'Y-m-d H:i:s\'');
            }
            else if (is_string($binding))
            {
                $bindings[$i] = "'$binding'";
            }
        }

        // Insert bindings into query
        $query = str_replace(array('%', '?'), array('%%', '%s'), $query);
        $query = vsprintf($query, $bindings);

        Log::info($query, $data);
    });
}
/*
|--------------------------------------------------------------------------
| Application Error Handler
|--------------------------------------------------------------------------
|
| Here you may handle any errors that occur in your application, including
| logging them or displaying custom views for specific errors. You may
| even register several error handlers to handle different types of
| exceptions. If nothing is returned, the default error view is
| shown, which includes a detailed stack trace during debug.
|
*/


App::error(function (Exception $exception, $code) {
    Log::error($exception);
    if(!App::runningInConsole()) {
        $checkpoint_service = ServiceLocator::getInstance()->getService(UtilsServiceCatalog::CheckPointService);
        if ($checkpoint_service) {
            $checkpoint_service->trackException($exception);
        }
        return Response::view('404', array(), 404);
    }
});


App::error(function (InvalidOpenIdMessageException $exception, $code) {
    Log::error($exception);
    if(!App::runningInConsole()) {
        $checkpoint_service = ServiceLocator::getInstance()->getService(UtilsServiceCatalog::CheckPointService);
        if ($checkpoint_service) {
            $checkpoint_service->trackException($exception);
        }
        return Response::view('404', array(), 404);
    }
});

App::error(function (InvalidOAuth2Request $exception, $code) {
    Log::error($exception);
    if(!App::runningInConsole()) {
        $checkpoint_service = ServiceLocator::getInstance()->getService(UtilsServiceCatalog::CheckPointService);
        if ($checkpoint_service) {
            $checkpoint_service->trackException($exception);
        }
        return Response::view('404', array(), 404);
    }
});



/*
|--------------------------------------------------------------------------
| Maintenance Mode Handler
|--------------------------------------------------------------------------
|
| The "down" Artisan command gives you the ability to put an application
| into maintenance mode. Here, you will define what is displayed back
| to the user if maintenace mode is in effect for this application.
|
*/

App::down(function () {
    return Response::make("Be right back!", 503);
});

/*
|--------------------------------------------------------------------------
| Require The Filters File
|--------------------------------------------------------------------------
|
| Next we will load the filters file for the application. This gives us
| a nice separate location to store our route and application filter
| definitions instead of putting them all in the main routes file.
|
*/

require app_path() . '/filters.php';
require app_path() . '/validators.php';