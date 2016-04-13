<?php namespace App\Http\Controllers;
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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Redirect;
use App\Http\Controllers\OpenId\OpenIdController;
use App\Http\Controllers\OpenId\DiscoveryController;

/**
 * Class HomeController
 * @package App\Http\Controllers
 */
class HomeController extends OpenIdController
{

    private $discovery;

    public function __construct(DiscoveryController $discovery)
    {
        $this->discovery = $discovery;
    }

    public function index()
    {

        if ($this->isDiscoveryRequest())
            return $this->discovery->idp();
        if (Auth::guest()) {
            Session::flush();
            Session::regenerate();
            return View::make("home");
        }
        else
            return Redirect::action("UserController@getProfile");
    }
}