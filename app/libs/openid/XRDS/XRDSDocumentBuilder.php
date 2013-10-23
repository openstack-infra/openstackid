<?php
namespace openid\XRDS;
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/14/13
 * Time: 2:21 PM
 * To change this template use File | Settings | File Templates.
 */

class XRDSDocumentBuilder {

    private $elements;
    private $canonical_id;

    const ContentType ='application/xrds+xml';
    const XRDNamespace ='xri://$xrd*($v*2.0)';
    const XRDSNamespace ='xXRDSServiceri://$xrds';

    public function __construct($elements,$canonical_id=null){
        $this->elements = $elements;
        $this->canonical_id = $canonical_id;
    }

    public function render(){
        $XRDNamespace = self::XRDNamespace;
        $XRDSNamespace = self::XRDSNamespace;
        $header = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<xrds:XRDS xmlns:xrds=\"{$XRDSNamespace}\" xmlns=\"{$XRDNamespace}\">\n<XRD>\n";
        $footer = "</XRD>\n</xrds:XRDS>";
        $xrds = $header;
        if(!is_null($this->canonical_id)){
            $xrds .= "<CanonicalID>{$this->canonical_id}</CanonicalID>\n";
        }
        foreach($this->elements as $service){
            $xrds .= $service->render();
        }
        $xrds .= $footer;
        return $xrds;
    }
}