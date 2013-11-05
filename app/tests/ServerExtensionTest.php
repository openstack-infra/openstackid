<?php

class ServerExtensionTest extends TestCase
{

    public function testAddServerExtension()
    {
        $new_ext = new ServerExtension();
        $new_ext->name = 'AX';
        $new_ext->description = 'OpenID service extension for exchanging identity information between endpoints';
        $new_ext->namespace = 'http://openid.net/srv/ax/1.0';
        $new_ext->active = true;
        $new_ext->extension_class = '';
        $new_ext->save();
        $ax = ServerExtension::where('name', '=', 'AX')->firstOrFail();
        $this->assertTrue($ax !== null);
    }
}