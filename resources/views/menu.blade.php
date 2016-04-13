<nav class="navbar navbar-default navbar-static-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#"></a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
            <ul id='main-menu' class="nav navbar-nav">
                <li id="profile"><a href='{{ URL::action("UserController@getProfile") }}'>Settings</a></li>
                <li id="oauth2-console" class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">OAUTH2 Console<b class="caret"></b></a>
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
                                <li class="dropdown-header">OAUTH2</li>
                                <li><a href='{{URL::action("AdminController@listServerPrivateKeys")}}'>Private Keys</a></li>
                                <li><a href='{{URL::action("AdminController@listResourceServers")}}'>Resource Servers</a></li>
                                <li><a href='{{URL::action("AdminController@listApiScopeGroups")}}'>Api Scope Groups</a></li>
                                <li><a href='{{URL::action("AdminController@listLockedClients")}}'>Clients</a></li>
                                <li role="separator" class="divider"></li>
                            @endif
                            @if($is_openstackid_admin)
                                <li class="dropdown-header">Server</li>
                                <li><a href='{{URL::action("AdminController@listLockedUsers")}}'>Users</a></li>
                                <li><a href='{{URL::action("AdminController@listBannedIPs")}}'>Banned IPs</a></li>
                                <li><a href='{{URL::action("AdminController@listServerConfig")}}'>Server Configuration</a></li>
                            @endif
                        </ul>
                    </li>
                @endif
                <li><a href='{{ URL::action("UserController@logout") }}'>Logout</a></li>
            </ul>
        </div><!--/.nav-collapse -->
    </div>
</nav>