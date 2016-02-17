<?php
/**
 * Created by PhpStorm.
 * User: smarcet
 * Date: 11/28/13
 * Time: 3:11 PM
 */

namespace strategies;

/**
 * Interface ILoginStrategy
 * @package strategies
 */
interface ILoginStrategy
{
    /**
     * @return mixed
     */
    public function  getLogin();

    /**
     * @return mixed
     */
    public function  postLogin();

    /**
     * @return mixed
     */
    public function  cancelLogin();

    /**
     * @param array $params
     * @return mixed
     */
    public function errorLogin(array $params);
} 