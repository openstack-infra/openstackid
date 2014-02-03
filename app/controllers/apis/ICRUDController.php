<?php


interface ICRUDController {

    public function get($id);
    public function create();
    public function getByPage();
    public function delete($id);
    public function update();

}