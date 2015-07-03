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

namespace services\oauth2;

use oauth2\models\IAssymetricKey;
use oauth2\services\IServerPrivateKey;
use oauth2\services\IServerPrivateKeyService;
use oauth2\repositories\IServerPrivateKeyRepository;
use utils\db\ITransactionService;
use ServerPrivateKey;
use DB;
use Crypt_RSA;
use ValidationException;

/**
 * Class ServerPrivateKeyService
 * @package services\oauth2
 */
final class ServerPrivateKeyService extends AssymetricKeyService implements IServerPrivateKeyService
{

    /**
     * @var Crypt_RSA
     */
    private $rsa;

    public function __construct
    (
        IServerPrivateKeyRepository $repository,
        ITransactionService $tx_service
    )
    {
        parent::__construct($repository, $tx_service);
        $this->rsa = new Crypt_RSA();
    }

    /**
     * @param array $params
     * @return IAssymetricKey
     * @throws ValidationException
     */
    public function register(array $params)
    {
        $rsa = $this->rsa;
        $repository = $this->repository;

        return $this->tx_service->transaction(function() use($params, $rsa, $repository)
        {
            $pem      = isset($params['pem_content']) ? $params['pem_content'] : '';
            $password = $params['password'];

            $old_active_key = $repository->getByValidityRange
            (
                $params['type'],
                $params['usage'],
                $params['alg'],
                new \DateTime($params['valid_from']),
                new \DateTime($params['valid_to'])
            )->first();


            if(empty($pem))
            {
                if(!empty($password))
                    $rsa->setPassword($password);
                /**
                 * array(
                 *    'privatekey' => $privatekey,
                 *   'publickey' => $publickey,
                 *   'partialkey' => false
                 *   );
                 */
                $res = $rsa->createKey(2048);
                $pem = $res['privatekey'];
            }


            $key = ServerPrivateKey::build
            (
                $params['kid'],
                new \DateTime($params['valid_from']),
                new \DateTime($params['valid_to']),
                $params['type'],
                $params['usage'],
                $params['alg'],
                $old_active_key ? false : $params['active'],
                $pem,
                $password
            );

            $repository->add($key);

            return $key;
        });
    }

}