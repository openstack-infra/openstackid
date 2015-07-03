<?php
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

namespace oauth2\responses;

use Illuminate\Support\Facades\HTML;

/**
 * http://openid.net/specs/oauth-v2-form-post-response-mode-1_0.html
 *
 * Class OAuth2PostResponse
 * @package oauth2\responses
 */
final class OAuth2PostResponse extends OAuth2Response
{
    /**
     * @var string
     */
    protected $return_to;

    const OAuth2PostResponseContentType = "text/html;charset=UTF-8";
    const OAuth2PostResponse            = "OAuth2PostResponse";

    public function __construct()
    {
        // Successful Responses: A server receiving a valid request MUST send a
        // response with an HTTP status code of 200.
        parent::__construct(self::HttpOkResponse, self::IndirectResponseContentType);

    }

    /**
     * @return string
     */
    public function getContent()
    {
        $return_to = $this->return_to;
        $fields  = '';
        if ($this->container !== null)
        {
            ksort($this->container);
            foreach ($this->container as $key => $value)
            {
                if (is_array($value))
                {
                    list($key, $value) = array($value[0], $value[1]);
                }

                $fields .= '<input type="hidden" name="'.$key.'" value="'.$value.'"/>';
            }
        }
        $content = <<<HTML
 <html>
   <head><title>Submit This Form</title></head>
   <body onload="javascript:document.forms[0].submit()">
    <form method="post" action="{$return_to}">
      $fields
    </form>
   </body>
  </html>
HTML;
     return $content;

    }

    public function getType()
    {
        return self::OAuth2PostResponse;
    }

    /**
     * @param string $return_to
     */
    public function setReturnTo($return_to)
    {
        $this->return_to = $return_to;
    }

    /**
     * @return string
     */
    public function getReturnTo()
    {
        return $this->return_to;
    }

    public function getContentType()
    {
        return self::OAuth2PostResponseContentType;
    }
}