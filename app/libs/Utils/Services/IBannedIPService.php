<?php namespace Utils\Services;

/**
 * Interface IBannedIPService
 * @package Utils\Services
 */
interface IBannedIPService {

    public function add($initial_hits, $exception_type);
    public function delete($ip);
    public function get($id);
    public function getByIP($ip);
    public function getByPage($page_nbr=1,$page_size=10,array $filters=array(),array $fields=array('*'));
} 