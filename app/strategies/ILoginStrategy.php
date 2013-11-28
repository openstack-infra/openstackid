<?php
/**
 * Created by PhpStorm.
 * User: smarcet
 * Date: 11/28/13
 * Time: 3:11 PM
 */

namespace strategies;


interface ILoginStrategy {
    public function  getLogin();
    public function  postLogin();
    public function  cancelLogin();
} 