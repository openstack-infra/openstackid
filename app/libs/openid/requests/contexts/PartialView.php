<?php

namespace openid\requests\contexts;

use string;

class PartialView
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