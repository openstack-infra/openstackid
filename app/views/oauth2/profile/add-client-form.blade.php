<form id="form-application" name="form-application">

    <div class="form-group">
        <label class="control-label" for="app_name">Application Name</label>
        <input type="text" class="form-control" name="app_name" id="app_name">
    </div>

    <div class="form-group">
        <label class="control-label" for="website">Application Web Site Url (optional)</label>
        <input type="text" name="website" class="form-control" id="website">
    </div>

    <div class="form-group">
        <label class="control-label" for="app_description">Application Description</label>
        <textarea style="resize: none;" rows="4" cols="50" name="app_description" id="app_description" class="form-control"></textarea>
    </div>

    <div class="form-group">
        <label class="control-label" for="application_type">Application Type</label>
        <span class="glyphicon glyphicon-info-sign accordion-toggle" aria-hidden="true" title="Web Server Application : The OpenstackId OAuth 2.0 endpoint supports web server applications that use languages and frameworks such as PHP, Java, Python, Ruby, and ASP.NET. These applications might access an Openstack API while the user is present at the application or after the user has left the application. This flow requires that the application can keep a secret.
Client Side (JS) : JavaScript-centric applications. These applications may access a Openstack API while the user is present at the application, and this type of application cannot keep a secret.
Service Account : The OpenstackId OAuth 2.0 Authorization Server supports server-to-server interactions. The requesting application has to prove its own identity to gain access to an API, and an end-user doesn't have to be involved. "></span>

            <select id="application_type" name="application_type" class="form-control">
                <option value="WEB_APPLICATION">Web Server Application</option>
                <option value="JS_CLIENT">Client Side (JS)</option>
                <option value="SERVICE">Service Account</option>
                <option value="NATIVE">Native Application</option>
            </select>
     </div>

    <div class="checkbox">
        <label>
            <input type="checkbox" id="active" name="active">&nbsp;Active
        </label>
    </div>
</form>