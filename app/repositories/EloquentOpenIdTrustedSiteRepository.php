<?php
namespace repositories;
use openid\repositories\IOpenIdTrustedSiteRepository;
use OpenIdTrustedSite;

class EloquentOpenIdTrustedSiteRepository implements  IOpenIdTrustedSiteRepository {

	private $openid_trusted_site;

	public function __construct(OpenIdTrustedSite $openid_trusted_site){
		$this->openid_trusted_site = $openid_trusted_site;
	}
	/**
	 * @param OpenIdTrustedSite $s
	 * @return bool
	 */
	public function add(OpenIdTrustedSite $s)
	{
		return $s->Save();
	}

	public function deleteById($id)
	{
		return $this->delete($this->get($id));
	}

	public function delete(OpenIdTrustedSite $s)
	{
		if(!is_null($s))
			return $s->delete();
		return false;
	}

	public function get($id)
	{
		return $this->openid_trusted_site->find($id);
	}

	/**
	 * @param int   $user_id
	 * @param array $sub_domains
	 * @param array $data
	 * @return array
	 */
	public function getMatchingOnesByUserId($user_id, array $sub_domains, array $data)
	{
		$query = $this->openid_trusted_site->where("user_id", "=", intval($user_id));
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
		return $query->get();
	}
}