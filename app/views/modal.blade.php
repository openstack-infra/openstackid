<!-- Modal -->
<div class="modal fade" id="{{$modal_id}}" tabindex="-1" role="dialog" aria-labelledby="{{$modal_id}}Label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="{{$modal_id}}Label">{{$modal_title}}</h4>
            </div>
            <div class="modal-body">
                @include($modal_form, $modal_form_data )
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary {{$modal_save_css_class}}">{{$modal_save_text}}</button>
            </div>
        </div>
    </div>
</div>