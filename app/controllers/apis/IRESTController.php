<?php


interface IRESTController {

    public function get($id);
    public function create();
    public function getByPage($page_nbr, $page_size);
    public function delete($id);
    public function update();

} 