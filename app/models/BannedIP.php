<?php
/**
 * Created by PhpStorm.
 * User: smarcet
 * Date: 10/30/13
 * Time: 5:38 PM
 */

class BannedIP extends Eloquent {
    protected $table = 'banned_ips';
    public $timestamps = false;
} 