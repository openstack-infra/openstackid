<?php
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
use Utils\Services\ICacheService;

/**
 * Class CacheServiceStub
 */
class CacheServiceStub implements ICacheService {

	private static $cache = array();

	/**
	 * Determine if a key exists
	 * @param $key
	 * @return bool
	 */
	public function exists($key)
	{
		return array_key_exists($key,self::$cache);
	}

	/**
	 * Delete a key
	 * @param $key
	 * @return mixed
	 */
	public function delete($key)
	{
		if(array_key_exists($key,self::$cache))
			unset(self::$cache[$key]);
	}

	/**
	 * Delete a key
	 * @param array $keys
	 * @return mixed
	 */
	public function deleteArray(array $keys)
	{
		foreach($keys as $key)
			$this->delete($key);
	}

	/**
	 * retrieves a hash
	 * @param       $name
	 * @param array $values
	 * @return array
	 */
	public function getHash($name, array $values)
	{
		if(array_key_exists($name,self::$cache))
			return self::$cache[$name];
	}

	/**
	 * save a hash, with an optional time to live
	 * @param       $name
	 * @param array $values
	 * @param int   $ttl
	 * @return mixed
	 */
	public function storeHash($name, array $values, $ttl = 0)
	{
		self::$cache[$name] = $values;
	}

	/**
	 * @param     $counter_name
	 * @param int $ttl
	 * @return mixed
	 */
	public function incCounter($counter_name, $ttl = 0)
	{
		if(!array_key_exists($counter_name,self::$cache))
		{
			self::$cache[$counter_name] = 0;
		}
		self::$cache[$counter_name] = intval(self::$cache[$counter_name]) + 1;
	}

	/**
	 * @param $counter_name
	 * @return mixed
	 */
	public function incCounterIfExists($counter_name)
	{
		if(array_key_exists($counter_name,self::$cache))
		{
			self::$cache[$counter_name] = intval(self::$cache[$counter_name]) + 1;
		}
	}

	public function addMemberSet($set_name, $member)
	{
		// TODO: Implement addMemberSet() method.
	}

	public function deleteMemberSet($set_name, $member)
	{
		// TODO: Implement deleteMemberSet() method.
	}

	public function getSet($set_name)
	{
		if(array_key_exists($set_name,self::$cache)){
			return self::$cache[$set_name];
		}
		return null;
	}

	public function getSingleValue($key)
	{
		if(array_key_exists($key,self::$cache)){
			return self::$cache[$key];
		}
		return null;
	}

	public function setSingleValue($key, $value, $ttl = 0)
	{
		self::$cache[$key]= $value;
	}

	/**
	 * adds a single value if given keys does not exists, with an optional
	 * time to live
	 * @param     $key
	 * @param     $value
	 * @param int $ttl
	 * @return mixed
	 */
	public function addSingleValue($key, $value, $ttl = 0)
	{
		if(!array_key_exists($key,self::$cache)){
			self::$cache[$key]= $value;
			return true;
		}
		return false;
	}

	/**
	 * Set time to live to a given key
	 * @param $key
	 * @param $ttl
	 * @return mixed
	 */
	public function setKeyExpiration($key, $ttl)
	{
		// TODO: Implement setKeyExpiration() method.
	}

	public function boot()
	{
		// TODO: Implement boot() method.
	}

	/**Returns the remaining time to live of a key that has a timeout.
	 * @param string $key
	 * @return int
	 */
	public function ttl($key)
	{
		// TODO: Implement ttl() method.
	}
}