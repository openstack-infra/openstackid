<?php namespace App\Http\Middleware;
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

use Symfony\Component\HttpFoundation\Request;

/**
* Class CORSRequestPreflightData
* @package App\Http\Middleware
*/
class CORSRequestPreflightData
{

	// ttl on seconds
	public static $cache_lifetime   = 10;
	public static $cache_attributes = array('sender', 'uri', 'origin', 'expected_method', 'expected_custom_headers',  'allows_credentials');

	/** Final HTTP request expected method */
	private $expected_method = null;
	/** Final HTTP request expected custom headers */
	private $expected_custom_headers = array();
	/** Current HTTP request uri */
	private $uri = null;
	/** Current HTTP request origin header */
	private $origin = null;
	/** Current Sender IP address */
	private $sender = null;

	/**
	 * @var bool
	 */
	private $allows_credentials;

	/**
	* @param Request $request
	* @param bool $allows_credentials
	*/
	public function __construct(Request $request, $allows_credentials)
	{
		$this->sender             = $request->getClientIp();
		$this->uri                = $request->getRequestUri();
		$this->origin             = $request->headers->get('Origin');
		$this->expected_method    = $request->headers->get('Access-Control-Request-Method');
		$this->allows_credentials = $allows_credentials;

		$tmp = $request->headers->get("Access-Control-Request-Headers");
		if (!empty($tmp))
		{
			$hs = explode(',', $tmp);
			foreach ($hs as $h)
			{
				array_push($this->expected_custom_headers, strtoupper(trim($h)));
			}
		}
	}

	/**
	 * @return array
	 */
	public function toArray()
	{
		$res                            = array();
		$res['sender']                  = $this->sender;
		$res['uri']                     = $this->uri;
		$res['origin']                  = $this->origin;
		$res['allows_credentials']      = $this->allows_credentials;
		$res['expected_method']         = $this->expected_method;
		$res['expected_custom_headers'] = implode(',', $this->expected_custom_headers);
		return $res;
	}

}