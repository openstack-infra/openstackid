<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/14/13
 * Time: 4:52 PM
 * To change this template use File | Settings | File Templates.
 */

namespace openid;


interface IOpenIdProtocol {
    /**
     * With OpenID 2.0, the relying party discovers the OpenID provider URL by requesting
     * the XRDS document (also called the Yadis document) with the content type application/xrds+xml;
     * this document may be available at the target URL and is always available for a target XRI.
     * @return mixed
     */
    public function getXRDSDiscovery();

    /**
     * With OpenID 1.0, the relying party then requests the HTML resource identified by the URL
     * and reads an HTML link tag to discover the OpenID provider's URL
     * (e.g. http://openid.example.org/openid-auth.php). The relying party also discovers whether to use a
     * delegated identity
     * @return mixed
     */
    public function getHtmlDiscovery();

    /**
    * @param OpenIdMessage $openIdMessage
    * @return mixed
    */
    public function HandleOpenIdMessage(OpenIdMessage $openIdMessage);
}