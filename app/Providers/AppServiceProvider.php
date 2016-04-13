<?php namespace App\Providers;
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
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Monolog\Handler\NativeMailerHandler;
use Illuminate\Support\Facades\Validator;
use Validators\CustomValidator;

/**
 * Class AppServiceProvider
 * @package App\Providers
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $monolog = Log::getMonolog();

        foreach($monolog->getHandlers() as $handler) {
            $handler->setLevel(Config::get('log.level', 'error'));
        }

        //set email log
        $to   = Config::get('log.to_email');
        $from = Config::get('log.from_email');

        if (!empty($to) && !empty($from)) {

            $subject  = 'openstackid error';
            $mono_log = Log::getMonolog();
            $handler  = new NativeMailerHandler($to, $subject, $from);

            $handler->setLevel(Config::get('log.email_level', 'error'));
            $mono_log->pushHandler($handler);
        }


        Validator::resolver(function($translator, $data, $rules, $messages)
        {
            return new CustomValidator($translator, $data, $rules, $messages);
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
