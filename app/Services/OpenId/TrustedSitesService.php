<?php namespace Services\OpenId;
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

use Exception;
use Models\OpenId\OpenIdTrustedSite;
use OpenId\Repositories\IOpenIdTrustedSiteRepository;
use OpenId\Exceptions\OpenIdInvalidRealmException;
use OpenId\Helpers\OpenIdUriHelper;
use OpenId\Models\IOpenIdUser;
use OpenId\Models\ITrustedSite;
use OpenId\Services\ITrustedSitesService;
use Utils\Services\IAuthService;
use Utils\Services\ILogService;

/**
 * Class TrustedSitesService
 * @package Services\OpenId
 */
class TrustedSitesService implements ITrustedSitesService
{
	/**
	 * @var IOpenIdTrustedSiteRepository
	 */
	private $repository;
	/**
	 * @var ILogService
	 */
	private $log_service;

	/**
	 * @param IOpenIdTrustedSiteRepository $repository
	 * @param ILogService                  $log_service
	 */
	public function __construct(IOpenIdTrustedSiteRepository $repository,  ILogService $log_service)
	{
		$this->repository          = $repository;
		$this->log_service         = $log_service;
	}

	/**
	 * @param IOpenIdUser $user
	 * @param             $realm
	 * @param             $policy
	 * @param array       $data
	 * @return bool|ITrustedSite
	 * @throws Exception
	 */
	public function addTrustedSite(IOpenIdUser $user, $realm, $policy, $data = array())
	{
		try {

			if (!OpenIdUriHelper::isValidRealm($realm))
				throw new OpenIdInvalidRealmException(sprintf('realm %s is invalid', $realm));
			$site          = new OpenIdTrustedSite;
			$site->realm   = $realm;
			$site->policy  = $policy;
			$site->user_id = $user->getId();
			$site->data    = json_encode($data);
			return $this->repository->add($site)?$site:false;

		} catch (Exception $ex) {
			$this->log_service->error($ex);
			throw $ex;
		}
		return false;
	}

	/**
	 * @param $id
	 * @return bool
	 */
	public function delTrustedSite($id)
	{
		try {
			return $this->repository->deleteById($id);
		} catch (Exception $ex) {
			$this->log_service->error($ex);
		}
	}

	/**
	 * @param IOpenIdUser $user
	 * @param             $realm
	 * @param array       $data
	 * @return array|mixed
	 * @throws Exception
	 */
	public function getTrustedSites(IOpenIdUser $user, $realm, $data = array())
	{
		$res = array();
		try {

			if (!OpenIdUriHelper::isValidRealm($realm))
				throw new OpenIdInvalidRealmException(sprintf('realm %s is invalid', $realm));
			//get all possible sub-domains
			$sub_domains = $this->getSubDomains($realm);
			$sites       = $this->repository->getMatchingOnesByUserId($user->getId(),$sub_domains,$data);
			//iterate over all retrieved sites and check the set policies by user
			foreach ($sites as $site) {
				$policy = $site->getAuthorizationPolicy();
				//if denied then break
				if ($policy == IAuthService::AuthorizationResponse_DenyForever) {
					array_push($res, $site);
					break;
				}
				$trusted_data = $site->getData();
				$diff = array_diff($data, $trusted_data);
				//if pre approved data is contained or equal than a former one
				if (count($diff) == 0) {
					array_push($res, $site);
					break;
				}
			}
		} catch (Exception $ex) {
			$this->log_service->error($ex);
			throw $ex;
		}
		return $res;
	}

	/**
	 * Get all possible sub-domains for a given url
	 * @param $url
	 * @return array
	 */
	private function getSubDomains($url)
	{
		$res = array();
		$url = strtolower($url);
		$scheme = $this->getScheme($url);
		//add entire url as first domain
		array_push($res, $url);
		$ends_with_slash = substr($url, -1) == '/';
		$url = parse_url($url);
		$authority = $url['host'];
		$components = explode('.', $authority);
		$len = count($components);

		for ($i = 0; $i < $len; $i++) {
			if ($components[$i] == '*') continue;
			$str = '';
			for ($j = $i; $j < $len; $j++)
				$str .= $components[$j] . '.';
			$str = trim($str, '.');
			$str = $ends_with_slash ? $str . '/' : $str;
			array_push($res, $scheme . '*.' . $str);
		}
		return $res;
	}

	/**
	 * @param string $url
	 * @return string
	 */
	private function getScheme($url)
	{
		$url = strtolower($url);
		$url = parse_url($url);
		$scheme = 'http://';
		if (isset($url['scheme']) && !empty($url['scheme'])) {
			$scheme = $url['scheme'] . '://';
		}
		return $scheme;
	}

}