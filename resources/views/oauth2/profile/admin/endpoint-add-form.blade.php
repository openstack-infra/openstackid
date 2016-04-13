<form id="form-endpoint" name="form-endpoint">
    <div class="form-group">
        <label for="name">Name</label>
        <input class="form-control" type="text" name="name" id="name">
    </div>
    <div class="form-group">
        <label for="description">Description</label>
        <textarea class="form-control" style="resize: none;" rows="4" cols="50" name="description" id="description"></textarea>
    </div>
    <div class="form-group">
        <label for="route">Route&nbsp;<span aria-hidden="true"
                                            class="glyphicon glyphicon-info-sign pointable"
                                            title=''></span></label>
        <input class="form-control" type="text" name="route" id="route">
    </div>
    <div class="form-group">
        <label for="rate_limit">Rate Limit (Per Hour)&nbsp;<span aria-hidden="true"
                                                                 class="glyphicon glyphicon-info-sign pointable"
                                                                 title=''></span></label>
        <input class="form-control" type="number" name="rate_limit" id="rate_limit">
    </div>
    <div class="form-group">
        <label for="http_method">HTTP Method&nbsp;<span aria-hidden="true"
                                                        class="glyphicon glyphicon-info-sign pointable"
                                                        title=''></span></label>
        <select class="form-control" name="http_method" id="http_method">
            <option value="GET">GET</option>
            <option value="POST">POST</option>
            <option value="PUT">PUT</option>
            <option value="DELETE">DELETE</option>
        </select>
    </div>
    <div class="checkbox">
        <label>
            <input type="checkbox" id="allow_cors" name="allow_cors">&nbsp;Allows CORS&nbsp;<span aria-hidden="true"
                                                                                                  class="glyphicon glyphicon-info-sign pointable"
                                                                                                  title=''></span>
        </label>
    </div>
    <div class="checkbox">
        <label>
            <input type="checkbox" id="active" name="active">&nbsp;Active
        </label>
    </div>
</form>