<div class="row">

    <div class="col-md-12">
        <h4>Client Credentials</h4>
        <hr/>
        <div class="row">
            <div class="col-md-12">
                <label for="client_id" class="label-client-secret">Client ID</label>
                <span id="client_id">{{ $client->client_id }}</span>
            </div>
        </div>
        @if($client->client_type == oauth2\models\IClient::ClientType_Confidential)
            <div class="row">
                <div class="col-md-12">
                    <label for="client_secret" class="label-client-secret">Client Secret</label>
                    <span id="client_secret">{{ $client->client_secret }}</span>
                    {{ HTML::link(URL::action("ClientApiController@regenerateClientSecret",array("id"=>$client->id)),'Regenerate',array('class'=>'btn btn-default btn-md active regenerate-client-secret','title'=>'Regenerates Client Secret')) }}
                </div>
            </div>
            @if($client->application_type == oauth2\models\IClient::ApplicationType_Web_App)
                <div class="row">
                    <div class="col-md-12">
                        <label class="label-client-secret">Client Settings</label>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox"
                                @if ($client->use_refresh_token)
                                       checked
                                       @endif
                                       id="use-refresh-token">
                                Use Refresh Tokens
                                &nbsp;<span class="glyphicon glyphicon-info-sign accordion-toggle"
                                            aria-hidden="true"></span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox"
                                @if ($client->rotate_refresh_token)
                                       checked
                                       @endif
                                       id="use-rotate-refresh-token-policy">
                                Use Rotate Refresh Token Policy
                                &nbsp;<span class="glyphicon glyphicon-info-sign accordion-toggle"
                                            aria-hidden="true"></span>
                            </label>
                        </div>
                    </div>
                </div>
            @endif
        @endif
        <h4>Client Data</h4>
        <hr/>
        <div class="row">
            <div class="col-md-12">
                <form id="form-application-main-data" name="form-application-main-data">

                    <div class="form-group">
                        <label for="website">Application Web Site Url (optional)</label>
                        <input class="form-control" type="text" name="website" id="website"
                               value="{{$client->website}}">
                    </div>
                    <div class="form-group">
                        <label for="logo_uri">Application Logo Url (optional)</label>
                        <input class="form-control" type="text" name="logo_uri" id="logo_uri"
                               value="{{$client->logo_uri}}">
                    </div>
                    <div class="form-group">
                        <label for="tos_uri">Application Term of Service Url (optional)</label>
                        <input class="form-control" type="text" name="tos_uri" id="tos_uri"
                               value="{{$client->tos_uri}}">
                    </div>
                    <div class="form-group">
                        <label for="policy_uri">Application Policy Url (optional)</label>
                        <input class="form-control" type="text" name="policy_uri" id="policy_uri"
                               value="{{$client->policy_uri}}">
                    </div>
                    <div class="form-group">
                        <label for="app_description">Application Description</label>
                        <textarea class="form-control" style="resize: none;" rows="4" cols="50" name="app_description"
                                  id="app_description">{{$client->app_description}}</textarea>
                    </div>
                    <div class="form-group">
                        <label for="contacts">Contact Emails (optional)</label>
                        <input type="text" name="contacts" id="contacts" value="{{$client->contacts}}"
                               style="width: 100%"></input>
                    </div>
                    <input type="hidden" id="id" name="id" value="{{$client->id}}"/>
                    <input type="hidden" id="user_id"   name="user_id" value="{{$client->user_id}}"/>
                    <button type="submit" class="btn btn-default btn-md active">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>

@section('scripts')
    {{ HTML::script('assets/js/oauth2/profile/edit-client-data.js') }}
@append