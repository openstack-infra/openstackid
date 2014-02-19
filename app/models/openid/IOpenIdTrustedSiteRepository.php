<?php
namespace openid\repositories;
use OpenIdTrustedSite;

/**
 * Interface IOpenIdTrustedSiteRepository
 * @package openid\repositories
 */
interface  IOpenIdTrustedSiteRepository {
	/**
	 * @param OpenIdTrustedSite $s
	 * @return bool
	 */
	public function add(OpenIdTrustedSite $s);
	public function deleteById($id);
	public function delete(OpenIdTrustedSite $s);
	public function get($id);

	/**
	 * @param int   $user_id
	 * @param array $sub_domains
	 * @param array $data
	 * @return array
	 */
	public function getMatchingOnesByUserId($user_id, array $sub_domains, array $data);

} 