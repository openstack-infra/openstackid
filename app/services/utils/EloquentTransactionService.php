<?php
namespace services\utils;


use Closure;
use utils\db\ITransactionService;
use DB;

/**
 * Class EloquentTransactionService
 * @package services\utils
 */
class EloquentTransactionService implements ITransactionService {

	/**
	 * Execute a Closure within a transaction.
	 *
	 * @param  Closure $callback
	 * @return mixed
	 *
	 * @throws \Exception
	 */
	public function transaction(Closure $callback)
	{
		return DB::transaction($callback);
	}
}