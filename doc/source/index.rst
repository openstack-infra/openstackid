=======================================
Welcome to OpenStackId's documentation!
=======================================

Introduction
============

OpenStackID Idp (Identity Provider) supports the `OpenID 2.0`_ protocol
providing authentication support as an OpenID provider.

also supports the following OpenID extensions:

* `OpenID Attribute Exchange 1.0`_
    Allows web developers to access, with the
    user's approval, certain user information stored with OpenStack DB,
    including user name and email address
* `OpenID Simple Registration Extension 1.0`_
    OpenID Simple Registration is
    an extension to the OpenID Authentication protocol that allows for very
    light-weight profile exchange. It is designed to pass eight commonly
    requested pieces of information when an End User goes to register a new
    account with a web service.
* `OpenID OAuth2 Extension`_
    OpenID+OAuth Hybrid protocol lets web developers
    combine an OpenID request with an OAuth authentication request.
    This extension is useful for web developers who use both OpenID and OAuth,
    particularly in that it simplifies the process for users by requesting their
    approval once instead of twice. In this way, the user can approve login and
    service access at the same time.

.. _OpenID 2.0: http://openid.net/specs/openid-authentication-2_0.html
.. _OpenID Attribute Exchange 1.0: http://openid.net/specs/openid-attribute-exc
    hange-1_0.htm
.. _OpenID Simple Registration Extension 1.0: http://openid.net/specs/openid-si
    mple-registration-extension-1_0.html
.. _OpenID OAuth2 Extension: http://step2.googlecode.com/svn/spec/openid_oauth_
    extension/latest/openid_oauth_extension.html

Its also support `The OAuth 2.0 Authorization Framework <http://tools.ietf.org/
html/rfc6749>`_ , turning OpenStackID Server in a Combined Provider (A web
service that is simultaneously an OpenID Identity Provider (OP) and an OAuth2
Service Provider (SP).).

:OAUTH2 Grants Support:

* `Authorization Code Grant <http://tools.ietf.org/html/rfc6749#section-4.1>`_
* `Implicit Grant <http://tools.ietf.org/html/rfc6749#section-4.2>`_
* `Client Credentials Grant <http://tools.ietf.org/html/rfc6749#section-4.4>`_

Table of contents
=================

Developer docs
--------------

.. toctree::
   :maxdepth: 2

   openid
   oauth2