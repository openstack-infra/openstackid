<?php namespace OpenId\Requests\Contexts;
/**
 * Class PartialView
 * @package OpenId\Requests\Contexts
 */
final class PartialView
{
    /**
     * @var string`
     */
    private $name;
    /**
     * @var array
     */
    private $data;

    /**
     * PartialView constructor.
     * @param string $name
     * @param array|null $data
     */
    public function __construct($name, array $data = null)
    {
        $this->name = $name;
        $this->data = $data;
    }

    /**
     * @return array|null
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}