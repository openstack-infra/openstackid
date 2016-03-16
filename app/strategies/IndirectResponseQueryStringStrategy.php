<?php


namespace strategies;

use utils\IHttpResponseStrategy;
use Redirect;
use Response;
use Log;
/**
 * Class IndirectResponseQueryStringStrategy
 * Redirect and http response using a 302 adding params on query string
 * @package strategies
 */
class IndirectResponseQueryStringStrategy implements IHttpResponseStrategy
{

    /**
     * @param $response
     * @return mixed
     */
    public function handle($response)
    {
        $query_string = $response->getContent();
        $return_to    = $response->getReturnTo();

        if (is_null($return_to) || empty($return_to)) {
            return Response::view('404', array(), 404);
        }
        $return_to = (strpos($return_to, "?") == false) ? $return_to . "?" . $query_string : $return_to . "&" . $query_string;
        Log::debug(sprintf("IndirectResponseQueryStringStrategy: return_to %s", $return_to));
        return Redirect::to($return_to)
            ->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
            ->header('Pragma','no-cache');
    }
}