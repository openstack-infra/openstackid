<?php

namespace strategies;

use utils\IHttpResponseStrategy;
use Redirect;
use Response;

/**
 * Class IndirectResponseUrlFragmentStrategy
 * Redirect and http response using a 302 adding params on url fragment
 * @package strategies
 */
class IndirectResponseUrlFragmentStrategy implements IHttpResponseStrategy
{

    /**
     * @param $response
     * @return mixed
     */
    public function handle($response)
    {
        $fragment  = $response->getContent();
        $return_to = $response->getReturnTo();

        if (is_null($return_to) || empty($return_to)) {
            return Response::view('404', array(), 404);;
        }

        $return_to = (strpos($return_to, "#") == false) ? $return_to . "#" . $fragment : $return_to . "&" . $fragment;
        return Redirect::to($return_to);
    }
}