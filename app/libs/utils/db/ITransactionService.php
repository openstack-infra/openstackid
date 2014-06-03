<?php

namespace utils\db;

use Closure;

/**
 * Interface ITransactionService
 * @package utils\db
 */
interface ITransactionService {
	/**
	 * Execute a Closure within a transaction.
	 *
	 * @param  Closure  $callback
	 * @return mixed
	 *
	 * @throws \Exception
	 */
	public function transaction(Closure $callback);
} 