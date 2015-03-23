<?php

namespace openid\XRDS;

/**
 * Class XRDSDocumentBuilder
 * @package openid\XRDS
 */
final class XRDSDocumentBuilder
{

    const ContentType   = 'application/xrds+xml';
    const Charset       = 'charset=UTF-8';
    const XRDNamespace  = 'xri://$xrd*($v*2.0)';
    const XRDSNamespace = 'xri://$xrds';

    private $elements;
    private $canonical_id;

    public function __construct($elements, $canonical_id = null)
    {
        $this->elements     = $elements;
        $this->canonical_id = $canonical_id;
    }

    public function render()
    {
        $XRDNamespace = self::XRDNamespace;
        $XRDSNamespace = self::XRDSNamespace;
        $header = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<xrds:XRDS xmlns:xrds=\"{$XRDSNamespace}\" xmlns=\"{$XRDNamespace}\">\n<XRD>\n";
        $footer = "</XRD>\n</xrds:XRDS>";
        $xrds = $header;
        if (!is_null($this->canonical_id)) {
            $xrds .= "<CanonicalID>{$this->canonical_id}</CanonicalID>\n";
        }
        foreach ($this->elements as $service) {
            $xrds .= $service->render();
        }
        $xrds .= $footer;
        return $xrds;
    }
}