<?php

namespace services\openid;

use Exception;
use openid\exceptions\OpenIdInvalidRealmException;
use openid\helpers\OpenIdUriHelper;
use openid\model\IOpenIdUser;
use openid\services\ITrustedSitesService;
use OpenIdTrustedSite;
use utils\services\IAuthService;
use utils\services\ILogService;

/**
 * Class TrustedSitesService
 * @package services\openid
 */
class TrustedSitesService implements ITrustedSitesService
{
	private $log_service;
	private $openid_trusted_site;

	/**
	 * @param OpenIdTrustedSite $openid_trusted_site
	 * @param ILogService       $log_service
	 */
	public function __construct(OpenIdTrustedSite $openid_trusted_site, ILogService $log_service)
	{
		$this->log_service = $log_service;
		$this->openid_trusted_site = $openid_trusted_site;
	}

	/**
	 * @param IOpenIdUser $user
	 * @param             $realm
	 * @param             $policy
	 * @param array       $data
	 * @return bool
	 * @throws \Exception
	 */
	public function addTrustedSite(IOpenIdUser $user, $realm, $policy, $data = array())
	{
		try {

			if (!OpenIdUriHelper::isValidRealm($realm))
				throw new OpenIdInvalidRealmException(sprintf('realm %s is invalid', $realm));

			$res = $this->openid_trusted_site->create(
				array(
					'realm' => $realm,
					'policy' => $policy,
					'user_id' => $user->getId(),
					'data' => json_encode($data)
				)
			);

		} catch (Exception $ex) {
			$this->log_service->error($ex);
			throw $ex;
		}
		return $res;
	}

	/**
	 * @param $id
	 */
	public function delTrustedSite($id)
	{
		try {
			$site = $this->openid_trusted_site->where("id", "=", $id)->first();
			if (!is_null($site)) $site->delete();
		} catch (Exception $ex) {
			$this->log_service->error($ex);
		}
	}

	/**
	 * @param IOpenIdUser $user
	 * @param             $realm
	 * @param array       $data
	 * @return array|mixed
	 * @throws \Exception
	 */
	public function getTrustedSites(IOpenIdUser $user, $realm, $data = array())
	{
		$sites = null;
		try {

			if (!OpenIdUriHelper::isValidRealm($realm))
				throw new OpenIdInvalidRealmException(sprintf('realm %s is invalid', $realm));

			//get all possible sub-domains
			$sub_domains = $this->getSubDomains($realm);
			//build query....
			$query = $this->openid_trusted_site->where("user_id", "=", intval($user->getId()));
			//add or condition for all given sub-domains
			if (count($sub_domains)) {
				$query = $query->where(function ($query) use ($sub_domains) {
					foreach ($sub_domains as $sub_domain) {
						$query = $query->orWhere(function ($query_aux) use ($sub_domain) {
							$query_aux->where('realm', '=', $sub_domain);
						});
					}
				});
			}
			//add conditions for all possible pre approved data
			foreach ($data as $value) {
				$query = $query->where("data", "LIKE", '%"' . $value . '"%');
			}
			$sites = $query->get();


			$res = array();
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
	 * @param $url
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

	public function getAllTrustedSitesByUser(IOpenIdUser $user)
	{
		$sites = null;
		try {
			$sites = $this->openid_trusted_site->where("user_id", "=", $user->getId())->get();
		} catch (Exception $ex) {
			$this->log_service->error($ex);
			throw $ex;
		}
		return $sites;
	}
}