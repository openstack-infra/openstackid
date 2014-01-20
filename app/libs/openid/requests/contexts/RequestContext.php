<?php

namespace openid\requests\contexts;


class RequestContext
{
    private $trusted_data;
    private $partial_views;

    public function __construct()
    {
        $this->partial_views = array();
        $this->trusted_data  = array();
    }

    public function addPartialView(PartialView $partial_view)
    {
        $this->partial_views[$partial_view->getName()] = $partial_view;
    }

    public function getPartials()
    {
        return $this->partial_views;
    }

    /**
     * Gets an associative array of current request trusted data
     * @return array
     */
    public function getTrustedData()
    {
        return $this->trusted_data;
    }

    public function cleanTrustedData(){
        $this->trusted_data = array();
    }

    public function setTrustedData($trusted_data)
    {
        $this->trusted_data = array_merge($this->trusted_data, $trusted_data);
    }
}