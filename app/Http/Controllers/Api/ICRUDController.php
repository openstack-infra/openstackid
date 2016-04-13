<?php namespace App\Http\Controllers;
/**
 * Interface ICRUDController
 * @package App\Http\Controllers
 */
interface ICRUDController {

    /**
     * @param $id
     * @return mixed
     */
    public function get($id);

    /**
     * @return mixed
     */
    public function create();

    /**
     * @return mixed
     */
    public function getByPage();

    /**
     * @param $id
     * @return mixed
     */
    public function delete($id);

    /**
     * @return mixed
     */
    public function update();

}