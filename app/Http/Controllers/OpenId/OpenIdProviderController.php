<?php namespace App\Http\Controllers\OpenId;

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
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use OpenId\Exceptions\InvalidOpenIdMessageException;
use OpenId\Exceptions\OpenIdBaseException;
use OpenId\Helpers\OpenIdErrorMessages;
use OpenId\IOpenIdProtocol;
use OpenId\OpenIdMessage;
use OpenId\Responses\OpenIdResponse;
use OpenId\Services\IMementoOpenIdSerializerService;
use OpenId\Strategies\OpenIdResponseStrategyFactoryMethod;

/**
 * Class OpenIdProviderController
 * @package App\Http\Controllers\OpenId
 */
class OpenIdProviderController extends Controller
{
    /**
     * @var IOpenIdProtocol
     */
    private $openid_protocol;
    /**
     * @var IMementoOpenIdSerializerService
     */
    private $memento_service;

    /**
     * @param IOpenIdProtocol $openid_protocol
     * @param IMementoOpenIdSerializerService $memento_service
     */
    public function __construct(IOpenIdProtocol $openid_protocol, IMementoOpenIdSerializerService $memento_service)
    {
        $this->openid_protocol = $openid_protocol;
        $this->memento_service = $memento_service;
    }

    /**
     * @return OpenIdResponse
     * @throws Exception
     * @throws InvalidOpenIdMessageException
     */
    public function endpoint()
    {
        try {
            $msg = new OpenIdMessage(Input::all());

            if (!$msg->isValid() && $this->memento_service->exists()) {
                $msg = OpenIdMessage::buildFromMemento($this->memento_service->load());
            }

            if (!$msg->isValid())
                throw new InvalidOpenIdMessageException(OpenIdErrorMessages::InvalidOpenIdMessage);

            //get response and manage it taking in consideration its type (direct or indirect)
            $response = $this->openid_protocol->handleOpenIdMessage($msg);

            if ($response instanceof OpenIdResponse) {
                $strategy = OpenIdResponseStrategyFactoryMethod::buildStrategy($response);
                return $strategy->handle($response);
            }
            return $response;
        }
        catch(OpenIdBaseException $ex1){
            Log::warning($ex1);
            return Response::view
            (
                'errors.400',
                array
                (
                    'error_code'        => "Bad Request",
                    'error_description' => $ex1->getMessage()
                ),
                400
            );
        }
        catch(Exception $ex){
            Log::error($ex);
            return Response::view
            (
                'errors.400',
                array
                (
                    'error_code'        => "Bad Request",
                    'error_description' => "Generic Error"
                ),
                400
            );
        }
    }
}