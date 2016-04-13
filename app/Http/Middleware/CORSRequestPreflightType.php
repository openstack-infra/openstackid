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

/**
 * Class CORSRequestPreflightType
 * @package App\Http\Middleware
 */
final class CORSRequestPreflightType
{

	/** HTTP request send by client to preflight a further 'Complex' request */
	const REQUEST_FOR_PREFLIGHT = 0;

	/** Normal HTTP request send by client that require preflight ie 'Complex' resquest in Preflight process */
	const COMPLEX_REQUEST = 1;

	/** Normal HTTP request send by client that do not require preflight ie 'Simple' resquest in Preflight process */

	const SIMPLE_REQUEST = 2;

	/** Cannot determine request type */

	const UNKNOWN = -1;

}
