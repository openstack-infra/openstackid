<h4>Logout Options</h4>
<hr/>
<form id="form-application-security-logout" name="form-application-security-logout">
    <div class="form-group">
        <label for="post_logout_redirect_uris">Post Logout Uris (optional)&nbsp;<span class="glyphicon glyphicon-info-sign accordion-toggle" aria-hidden="true" title=""></span></label>
        <input type="text" name="post_logout_redirect_uris" class="form-control" id="post_logout_redirect_uris"
               value="{{$client->post_logout_redirect_uris}}">
    </div>
    <div class="form-group">
        <label for="logout_uri">Logout Uri (optional)&nbsp;<span class="glyphicon glyphicon-info-sign accordion-toggle" aria-hidden="true" title=""></span></label>
        <input type="text" name="logout_uri" class="form-control" id="logout_uri"
               value="{{$client->logout_uri}}">
    </div>
    <div class="checkbox">
        <label>
            <input type="checkbox" id="logout_session_required" name="logout_session_required" @if ($client->logout_session_required)checked@endif>Session Required (Optional)&nbsp;<span class="glyphicon glyphicon-info-sign accordion-toggle" aria-hidden="true" title=""></span>
        </label>
    </div>
    <div class="checkbox">
        <label>
            <input type="checkbox" id="logout_use_iframe" name="logout_use_iframe" @if ($client->logout_use_iframe)checked@endif>Use IFrame (Optional)&nbsp;<span class="glyphicon glyphicon-info-sign accordion-toggle" aria-hidden="true" title=""></span>
        </label>
    </div>
    <input type="hidden" id="id" name="id" value="{{$client->id}}"/>
    <input type="hidden" id="user_id"   name="user_id" value="{{$client->user_id}}"/>
    <button type="submit" class="btn btn-default btn-md active">Save</button>
</form>
@section('scripts')
    {{ HTML::script('assets/js/oauth2/profile/edit-client-security-logout.js') }}
@append