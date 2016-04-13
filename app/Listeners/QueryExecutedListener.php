<?php namespace App\Listeners;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use DateTime;

class QueryExecutedListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  QueryExecuted  $event
     * @return void
     */
    public function handle(QueryExecuted $event)
    {
        if(Config::get("server.db_log_enabled", false)) {

            $query      = $event->sql;
 			$bindings   = $event->bindings;

            // Format binding data for sql insertion
            foreach ($bindings as $i => $binding) {
                if ($binding instanceof DateTime) {
                    $bindings[$i] = $binding->format('\'Y-m-d H:i:s\'');
                } else {
                    if (is_string($binding)) {
                        $bindings[$i] = "'$binding'";
                    }
                }
            }

            $time       = $event->time;
            $connection = $event->connectionName;
            $data       = compact('bindings', 'time', 'connection');
            // Insert bindings into query
            $query = str_replace(array('%', '?'), array('%%', '%s'), $query);
            $query = vsprintf($query, $bindings);
            Log::info($query, $data);
            //trace
            $trace = '';
            $entries = debug_backtrace();
            unset($entries[0]);
            foreach($entries as $entry){
                if(!isset($entry['file']) || !isset($entry['line'])) continue;
                $trace .= $entry['file'].' '.$entry['line'].PHP_EOL;
            }
            Log::debug($trace);

        }
    }
}
