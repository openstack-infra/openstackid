<?php

namespace openid\XRDS;

/**
 * Class XRDSService
 * XRDS Service Element
 * @package openid\XRDS
 */
final class XRDSService
{

    private $priority;
    private $type;
    private $uri;
    private $local_id;
    private $extensions;

    public function __construct($priority, $type, $uri, $extensions = array(), $local_id = null)
    {
        $this->priority = $priority;
        $this->type = $type;
        $this->uri = $uri;
        $this->local_id = $local_id;
        $this->extensions = $extensions;
    }

    public function render()
    {
        $local_id = empty($this->local_id) ? "" : "<LocalID>{$this->local_id}</LocalID>\n";

        $extensions = "";
        foreach ($this->extensions as $extension) {
            $extensions .= "<Type>{$extension}</Type>\n";
        }

        $element = "<Service priority=\"{$this->priority}\">\n<Type>{$this->type}</Type>\n{$extensions}<URI>{$this->uri}</URI>\n{$local_id}</Service>\n";
        return $element;
    }

    public function getPriority()
    {
        return $this->priority;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getUri()
    {
        return $this->uri;
    }

    public function getLocalId()
    {
        return $this->local_id;
    }
}