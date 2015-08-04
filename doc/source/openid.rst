===================
OpenID 2.0 endpoint
===================

To get the OpenStackID OpenID endpoint, perform discovery by sending a GET
HTTP request to https://openstackid.org. We recommend setting the
Accept header to "application/xrds+xml". OpenStackID returns an XRDS document
containing an OpenID provider endpoint URL.The endpoint address is
annotated as::

    <Service priority="0">

    <Type>http://specs.openid.net/auth/2.0/server</Type>

    <URI>{OpenStackID's login endpoint URI}</URI>

    </Service>

OpenID 2.0 request parameters
-----------------------------

Once you've acquired the OpenStackID endpoint, send authentication requests
to it, specifying the following parameters as relevant. Connect to the
endpoint by sending a request to the URL or by making an HTTP POST request.

+---------------------+--------------------------------------------------------------------------------------------------------------------------+
| Parameter           | Description                                                                                                              |
+=====================+==========================================================================================================================+
| openid.mode         | (required) Interaction mode. Specifies                                                                                   |
|                     | whether Openstack Id IdP may interact with the user to determine the outcome of the request.                             |
|                     | Valid values are:                                                                                                        |
|                     |                                                                                                                          |
|                     | * checkid_immediate (No interaction allowed)                                                                             |
|                     | * checkid_setup (Interaction allowed)                                                                                    |
|                     |                                                                                                                          |
+---------------------+--------------------------------------------------------------------------------------------------------------------------+
| openid.ns           | (required) Protocol version. Value identifying the OpenID protocol version being used.                                   |
|                     | This value should be "http://specs.openid.net/auth/2.0".                                                                 |
|                     |                                                                                                                          |
+---------------------+--------------------------------------------------------------------------------------------------------------------------+
| openid.return_to    | (required) Return URL. Value indicating the URL where the user should be returned to after signing in.                   |
|                     | Openstack Id Idp only supports HTTPS address types                                                                       |
|                     |                                                                                                                          |
+---------------------+--------------------------------------------------------------------------------------------------------------------------+
| openid.assoc_handle | (optional) Association handle. Set if an association was established between the relying party (web application) and the |
|                     | identity provider (Openstack).                                                                                           |
|                     | See OpenID specification Section 8.                                                                                      |
|                     |                                                                                                                          |
+---------------------+--------------------------------------------------------------------------------------------------------------------------+
| openid.claimed_id   | (required) Claimed identifier. This value must be set to "http://specs.openid.net/auth/2.0/identifier_select".           |
|                     | or to user claimed identity (user local identifier or user owned identity                                                |
|                     | [ex: custom html hosted on a owned domain set to html discover])                                                         |
|                     |                                                                                                                          |
+---------------------+--------------------------------------------------------------------------------------------------------------------------+
| openid.identity     | (required) Alternate identifier. This value must be set to http://specs.openid.net/auth/2.0/identifier_select.           |
|                     |                                                                                                                          |
+---------------------+--------------------------------------------------------------------------------------------------------------------------+
| openid.realm        | (required) Authenticated realm. Identifies the domain that the end user is being asked to trust.                         |
|                     | (Example: ``http://*.myexamplesite.com``) This value must be consistent with the domain defined in openid.return_to.     |
|                     |                                                                                                                          |
+---------------------+--------------------------------------------------------------------------------------------------------------------------+

Attribute exchange extension
----------------------------

+--------------------------+--------------------------------------------------------------------------------------------------------------------------+
| Parameter                | Description                                                                                                              |
+==========================+==========================================================================================================================+
| openid.ns.ax             |(required) Indicates request for user attribute information. This value must be set to "http://openid.net/srv/ax/1.0".    |
|                          |                                                                                                                          |
+--------------------------+--------------------------------------------------------------------------------------------------------------------------+
| openid.ax.mode           | (required) This value must be set to "fetch_request".                                                                    |
|                          |                                                                                                                          |
+--------------------------+--------------------------------------------------------------------------------------------------------------------------+
| openid.ax.required       | (required) Specifies the attribute being requested. Valid values include:                                                |
|                          | "country","email","firstname","language","lastname"                                                                      |
|                          | To request multiple attributes, set this parameter to a comma-delimited list of attributes.                              |
|                          |                                                                                                                          |
+--------------------------+--------------------------------------------------------------------------------------------------------------------------+
| openid.ax.type.country   | (optional) Requests the user's home country. This value must be set to "http://axschema.org/contact/country/home".       |
|                          |                                                                                                                          |
+--------------------------+--------------------------------------------------------------------------------------------------------------------------+
| openid.ax.type.email     | (optional) Requests the user's gmail address. This value must be set to "http://axschema.org/contact/email"              |
|                          |                                                                                                                          |
+--------------------------+--------------------------------------------------------------------------------------------------------------------------+
| openid.ax.type.firstname | (optional) Requests the user's first name. This value must be set to "http://axschema.org/namePerson/first".             |
|                          |                                                                                                                          |
+--------------------------+--------------------------------------------------------------------------------------------------------------------------+
| openid.ax.type.language  | (optional) Requests the user's preferred language. This value must be set to "http://axschema.org/pref/language".        |
|                          |                                                                                                                          |
+--------------------------+--------------------------------------------------------------------------------------------------------------------------+
| openid.ax.type.lastname  | (optional) Requests the user's last name. This value must be set to "http://axschema.org/namePerson/last".               |
|                          |                                                                                                                          |
+--------------------------+--------------------------------------------------------------------------------------------------------------------------+


Simple Registration Extension
-----------------------------

+--------------------------+---------------------------------------------------------------------------------------------------------------------------------+
| Parameter                | Description                                                                                                                     |
+==========================+=================================================================================================================================+
| openid.ns.sreg           | (required) Indicates request for user attribute information. This value must be set to "http://openid.net/extensions/sreg/1.1". |
|                          |                                                                                                                                 |
+--------------------------+---------------------------------------------------------------------------------------------------------------------------------+
| openid.sreg.required     | (required) Comma-separated list of field names which, if absent from the response, will prevent the Consumer from completing    |
|                          | the registration without End User interation. The field names are those that are specified in the Response Format,              |
|                          | with the "openid.sreg." prefix removed.                                                                                         |
|                          | Valid values include:                                                                                                           |
|                          | "country", "email", "firstname", "language", "lastname"                                                                         |
+--------------------------+---------------------------------------------------------------------------------------------------------------------------------+
| openid.sreg.optional     | (required) Comma-separated list of field names Fields that will be used by the Consumer, but whose absence will not prevent     |
|                          | the registration from completing. The field names are those that are specified in the Response Format, with the "openid.sreg."  |
|                          | prefix removed.                                                                                                                 |
|                          | Valid values include:                                                                                                           |
|                          | "country", "email", "firstname", "language", "lastname"                                                                         |
+--------------------------+---------------------------------------------------------------------------------------------------------------------------------+
| openid.sreg.policy_url   | (optional) A URL which the Consumer provides to give the End User a place to read about the how the profile data will be used.  |
|                          | The Identity Provider SHOULD display this URL to the End User if it is given.                                                   |
|                          |                                                                                                                                 |
+--------------------------+---------------------------------------------------------------------------------------------------------------------------------+


OAuth 2.0 Extension
-------------------

+------------------------------+---------------------------------------------------------------------------------------------------------------------------------+
| Parameter                    | Description                                                                                                                     |
+==============================+=================================================================================================================================+
| openid.ns.oauth              | (required) Indicates request for OAuth2. This value must be set to "http://specs.openid.net/extensions/oauth/2.0".              |
|                              |                                                                                                                                 |
+------------------------------+---------------------------------------------------------------------------------------------------------------------------------+
| openid.oauth.client_id       | (required) Identifies the client that is making the request. The value passed in this parameter must exactly match the value    |
|                              | shown in the OpenstackId OAUTH2 Console.                                                                                        |
|                              |                                                                                                                                 |
+------------------------------+---------------------------------------------------------------------------------------------------------------------------------+
| openid.oauth.scope           | (required) Identifies the Openstack API access that your application is requesting. The values passed in this parameter         |
|                              | inform the consent screen that is shown to the user.                                                                            |
|                              |                                                                                                                                 |
+------------------------------+---------------------------------------------------------------------------------------------------------------------------------+
| openid.oauth.state           | (required) Provides any state that might be useful to your application upon receipt of the response.                            |
|                              | The OpenstackId Authorization Server roundtrips this parameter, so your application receives the same value it sent.            |
|                              | Possible uses include redirecting the user to the correct resource in your site, nonces, and cross-site-request-forgery         |
|                              | mitigations.                                                                                                                    |
|                              |                                                                                                                                 |
+------------------------------+---------------------------------------------------------------------------------------------------------------------------------+
| openid.oauth.approval_prompt | (optional) Indicates whether the user should be re-prompted for consent. The default is auto, so a given user should only       |
|                              | see the consent page for a given set of scopes the first time through the sequence. If the value is force,                      |
|                              | then the user sees a consent page even if they previously gave consent to your application for a given set of scopes.           |
|                              |                                                                                                                                 |
+------------------------------+---------------------------------------------------------------------------------------------------------------------------------+
| openid.oauth.access_type     | (optional) Indicates whether your application needs to access a OpenstackId API when the user is not present at the browser.    |
|                              | This parameter defaults to "online". If your application needs to refresh access tokens when the user is not present at         |
|                              | the browser, then use "offline". This will result in your application obtaining a refresh token the first time your application |
|                              | exchanges an authorization code for a user.                                                                                     |
|                              |                                                                                                                                 |
+------------------------------+---------------------------------------------------------------------------------------------------------------------------------+


OpenID 2.0 request authentication response
------------------------------------------

Once OpenStackID accepts the authentication request, the user is redirected to
a OpenStackID authentication page. At this point the authentication sequence
takes over. On successful authentication, OpenStackID redirects the user back
to the URL specified in the openid.return_to parameter of the original request.
Response data is appended as query parameters, including a
OpenStackID-supplied identifier, user information, if requested, and an
OAuth 2.0 request token,
if requested. OpenStackID may redirect through an HTTP 302 status code to
the return URL, resulting in a GET request, or may cause the browser to issue
a POST request to the return URL, passing the OpenID 2.0 parameters in the
POST body. A website or application should be prepared to accept responses as
both GETs and POSTs.
If the user doesn't approve the authentication request, OpenStackID sends a
negative assertion to the requesting website.
