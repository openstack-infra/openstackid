<?php
$use_ssl = env('DB_USE_SSL', false);

$idp_db_config =  [
    'driver'    => 'mysql',
    'host'      => env('DB_HOST'),
    'database'  => env('DB_DATABASE'),
    'username'  => env('DB_USERNAME'),
    'password'  => env('DB_PASSWORD'),
    'port'      => env('DB_PORT', 3306),
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
];

$ss_db_config =  [
    'driver'    => env('SS_DB_DRIVER'),
    'host'      => env('SS_DB_HOST'),
    'database'  => env('SS_DATABASE'),
    'username'  => env('SS_DB_USERNAME'),
    'password'  => env('SS_DB_PASSWORD'),
    'port'      => env('SS_DB_PORT', 3306),
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
];

if($use_ssl){
    $idp_db_config['options'] = [
        PDO::MYSQL_ATTR_SSL_CA => env('DB_MYSQL_ATTR_SSL_CA','/etc/client-ssl/ca-cert.pem'),
        PDO::MYSQL_ATTR_SSL_KEY =>  env('DB_MYSQL_ATTR_SSL_KEY','/etc/client-ssl/client-key.pem'),
        PDO::MYSQL_ATTR_SSL_CERT  =>  env('DB_MYSQL_ATTR_SSL_CERT','/etc/client-ssl/client-cert.pem'),
        PDO::MYSQL_ATTR_SSL_CIPHER =>  env('DB_MYSQL_ATTR_SSL_CIPHER', 'DHE-RSA-AES256-SHA'),
    ];
    $ss_db_config['options'] = [
        PDO::MYSQL_ATTR_SSL_CA => env('DB_MYSQL_ATTR_SSL_CA','/etc/client-ssl/ca-cert.pem'),
        PDO::MYSQL_ATTR_SSL_KEY =>  env('DB_MYSQL_ATTR_SSL_KEY','/etc/client-ssl/client-key.pem'),
        PDO::MYSQL_ATTR_SSL_CERT  =>  env('DB_MYSQL_ATTR_SSL_CERT','/etc/client-ssl/client-cert.pem'),
        PDO::MYSQL_ATTR_SSL_CIPHER =>  env('DB_MYSQL_ATTR_SSL_CIPHER', 'DHE-RSA-AES256-SHA'),
    ];
}

return [

    /*
    |--------------------------------------------------------------------------
    | PDO Fetch Style
    |--------------------------------------------------------------------------
    |
    | By default, database results will be returned as instances of the PHP
    | stdClass object; however, you may desire to retrieve records in an
    | array format for simplicity. Here you can tweak the fetch style.
    |
    */

    'fetch' => PDO::FETCH_CLASS,

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => 'openstackid',

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [
        //primary DB
        'openstackid' => $idp_db_config,
        //secondary DB (SS OS)
        'ss' => $ss_db_config,
    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer set of commands than a typical key-value systems
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [

        'cluster' => false,

        'default' => [
            'host'       => env('REDIS_HOST'),
            'port'       => env('REDIS_PORT'),
            'database'   => env('REDIS_DB', 0),
            'password'   => env('REDIS_PASSWORD'),
        ],

    ],
];
