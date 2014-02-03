<div class="navbar">
    <div class="navbar-inner">
        <ul id='main-menu' class="nav">
            <li id="profile"><a href='{{ URL::action("UserController@getProfile") }}'>Profile</a></li>
            <li id="oauth2-console" class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    OAUTH2 Console
                    <b class="caret"></b>
                </a>
                <ul class="dropdown-menu">
                    <li><a href='{{URL::action("AdminController@listOAuth2Clients")}}'>OAUTH2 Applications</a></li>
                    <li><a href='{{URL::action("AdminController@editIssuedGrants")}}'>Issued OAUTH2 Grants</a></li>
                </ul>
            </li>
            @if($is_oauth2_admin || $is_openstackid_admin)
            <li id='server-admin' class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    Server Administration
                    <b class="caret"></b>
                </a>
                <ul class="dropdown-menu">
                    @if($is_oauth2_admin)
                    <li class="nav-header">OAUTH2</li>
                    <li><a href='{{URL::action("AdminController@listResourceServers")}}'>Resource Servers</a></li>
                    <li><a href='{{URL::action("AdminController@listLockedClients")}}'>Clients</a></li>
                    @endif
                    @if($is_openstackid_admin)
                    <li class="nav-header">Server</li>
                    <li><a href=''>Users</a></li>
                    <li><a href='{{URL::action("AdminController@listBannedIPs")}}'>Banned IPs</a></li>
                    <li><a href='{{URL::action("AdminController@listServerConfig")}}'>Server Configuration</a></li>
                    @endif
                </ul>
            </li>
            @endif
            <li><a href='{{ URL::action("UserController@logout") }}'>Logout</a></li>
        </ul>
    </div>
</div>
