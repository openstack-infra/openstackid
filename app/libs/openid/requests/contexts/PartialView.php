<?php

namespace openid\requests\contexts;

/**
 * Class PartialView
 * @package openid\requests\contexts
 */
final class PartialView
{

    private $name;
    private $data;

    public function __construct($name, array $data = null)
    {
        $this->name = $name;
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getName()
    {
        return $this->name;
    }
}