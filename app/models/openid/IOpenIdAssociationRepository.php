<?php
namespace openid\repositories;
use OpenIdAssociation;

/**
 * Interface IOpenIdAssociationRepository
 * @package openid\repositories
 */
interface  IOpenIdAssociationRepository {
	public function add(OpenIdAssociation $a);
	public function deleteById($id);
	public function delete(OpenIdAssociation $a);
	public function get($id);
	public function getByHandle($handle);
} 