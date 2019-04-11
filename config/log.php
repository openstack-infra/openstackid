<?php
/**
 * Copyright 2016 OpenStack Foundation
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
return array(
    /**
     * EMAIL ERROR LOG CONFIGURATION
     */
    //The receiver of the mail
    'to_email'   => env('LOG_EMAIL_TO'),
    //The sender of the mail
    'from_email'  => env('LOG_EMAIL_FROM'),
    //Log Level (debug, info, notice, warning, error, critical, alert)
    'level'         => env('LOG_LEVEL', 'error'),
    'email_level'   => env('LOG_EMAIL_LEVEL', 'error'),
    'email_subject' => env('LOG_EMAIL_SUBJECT', ''),
);