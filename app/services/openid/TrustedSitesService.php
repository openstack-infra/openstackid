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
use openid\repositories\IOpenIdTrustedSiteRepository;

/**
 * Class TrustedSitesService
 * @package services\openid
 */
class TrustedSitesService implements ITrustedSitesService
{

	private $repository;
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
	 * @return bool1|ITrustedSite
	 * @throws \Exception
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
	 * @throws \Exception
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

}