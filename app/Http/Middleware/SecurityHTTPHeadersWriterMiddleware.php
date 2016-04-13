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

use Closure;

/**
* Class SecurityHTTPHeadersWriterMiddleware
* https://www.owasp.org/index.php/List_of_useful_HTTP_headers
*
* @package App\Http\Middleware
*/
class SecurityHTTPHeadersWriterMiddleware
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @param  \Closure $next
	 * @return \Illuminate\Http\Response
	 */
	public function handle($request, Closure $next)
	{
		$response = $next($request);
		// https://www.owasp.org/index.php/List_of_useful_HTTP_headers
		$response->headers->set('X-content-type-options','nosniff');
		$response->headers->set('X-xss-protection','1; mode=block');
		//cache
		$response->headers->set('pragma','no-cache');
		$response->headers->set('Expires','-1');
		$response->headers->set('cache-control','no-store, must-revalidate, no-cache');
		return $response;
	}
}