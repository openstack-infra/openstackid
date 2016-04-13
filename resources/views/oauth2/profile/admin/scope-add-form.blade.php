<form id="form-scope" name="form-scope">
    <div class="form-group">
        <label for="name">Name</label>
        <input class="form-control" type="text" name="name" id="name">
    </div>
    <div class="form-group">
        <label for="short_description">Short Description</label>
        <textarea class="form-control" style="resize: none;" rows="2" cols="50" name="short_description"
                  id="short_description"></textarea>
    </div>
    <div class="form-group">
        <label for="description">Description</label>
        <textarea class="form-control" style="resize: none;" rows="4" cols="50" name="description" id="description"></textarea>
    </div>
    <div class="checkbox">
        <label>
            <input type="checkbox" id="default" name="default">&nbsp;Default
        </label>
    </div>
    <div class="checkbox">
        <label>
            <input type="checkbox" id="system" name="system">&nbsp;System
        </label>
    </div>
    <div class="checkbox">
        <label>
            <input type="checkbox" id="assigned_by_groups" name="assigned_by_groups">&nbsp;Assigned By Groups
        </label>
    </div>
    <div class="checkbox">
        <label>
            <input type="checkbox" id="active" name="active">&nbsp;Active
        </label>
    </div>
</form>