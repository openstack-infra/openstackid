<?php namespace OpenId\Requests\Contexts;
/**
 * Class RequestContext
 * @package OpenId\Requests\Contexts
 */
class RequestContext
{
    /**
     * @var array
     */
    private $trusted_data;
    /**
     * @var array
     */
    private $partial_views;

    public function __construct()
    {
        $this->partial_views = array();
        $this->trusted_data  = array();
    }

    /**
     * @param PartialView $partial_view
     * @return $this
     */
    public function addPartialView(PartialView $partial_view)
    {
        $this->partial_views[$partial_view->getName()] = $partial_view;
        return $this;
    }

    /**
     * @return array
     */
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

    /**
     * @return $this
     */
    public function cleanTrustedData(){
        $this->trusted_data = array();
        return $this;
    }

    /**
     * @param $trusted_data
     * @return $this
     */
    public function setTrustedData($trusted_data)
    {
        $this->trusted_data = array_merge($this->trusted_data, $trusted_data);
        return $this;
    }
}