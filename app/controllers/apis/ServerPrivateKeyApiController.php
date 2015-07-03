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

use oauth2\services\IServerPrivateKeyService;
use oauth2\repositories\IServerPrivateKeyRepository;
use utils\services\ILogService;
/**
 * Class ServerPrivateKeyApiController
 */
final class ServerPrivateKeyApiController extends AssymetricKeyApiController
{
    /**
     * @param IServerPrivateKeyRepository $repository
     * @param IServerPrivateKeyService $service
     * @param ILogService $log_service
     */
    public function __construct
    (
        IServerPrivateKeyRepository $repository,
        IServerPrivateKeyService $service,
        ILogService $log_service
    )
    {
        parent::__construct($repository, $service, $log_service);
    }

    /**
     * @return mixed
     */
    public function create()
    {
        try
        {

            $values = Input::All();
            // Build the validation constraint set.
            $rules = array(
                'kid'         => 'required|text|min:5|max:255',
                'active'      => 'required|boolean',
                'valid_from'  => 'date_format:m/d/Y',
                'valid_to'    => 'date_format:m/d/Y|after:valid_from',
                'pem_content' => 'sometimes|required|private_key_pem:password|private_key_pem_length:password',
                'usage'       => 'required|public_key_usage',
                'type'        => 'required|public_key_type',
                'alg'         => 'required|key_alg:type',
                'password'    => 'min:5|max:255|private_key_password:pem_content',
            );

            // Create a new validator instance.
            $validation = Validator::make($values, $rules);

            if ($validation->fails())
            {
                $messages   = $validation->messages()->toArray();
                return $this->error400(array('error' => 'validation', 'messages' => $messages));
            }

            $private_key = $this->service->register($values);

            return $this->created(array('id' => $private_key->getId()));

        }
        catch(ValidationException $ex1)
        {
            return $this->error400(array('error' => $ex1->getMessage()));
        }
        catch (Exception $ex)
        {
            $this->log_service->error($ex);

            return $this->error500($ex);
        }
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function update($id)
    {
        return $this->_update($id);
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function delete($id)
    {
        return $this->_delete($id);
    }

}