<?php

namespace repositories;

use openid\repositories\IOpenIdAssociationRepository;
use OpenIdAssociation;

/**
 * Class EloquentOpenIdAssociationRepository
 * @package repositories
 */

class EloquentOpenIdAssociationRepository implements IOpenIdAssociationRepository {

	private $association;

	public function __construct(OpenIdAssociation $association){
		$this->association = $association;
	}

	public function add(OpenIdAssociation $a)
	{
		return $a->Save();
	}

	public function deleteById($id)
	{
		return $this->delete($this->get($id));
	}

	public function getByHandle($handle)
	{
		return $this->association->where('identifier', '=', $handle)->first();
	}

	public function delete(OpenIdAssociation $a)
	{
		if(!is_null($a))
			return $a->delete();
		return false;
	}

	public function get($id)
	{
		return $this->association->find($id);
	}
}