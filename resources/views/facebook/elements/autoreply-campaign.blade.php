<style type="text/css">
    .fieldGroup {
        background: #f4f4f4;
        padding: 12px;
        border-radius: 5px;
    }
</style>
<div class="col-lg-12">
    <input type="text" hidden name="page_id" value="{{ $page->id }}">
    <div class="form-group fieldGroup">
        <div class="input-group">
            <label class="font-bold">Generic comment reply</label>
            <textarea style="width: 90%" class="form-control" name="generic_comment_reply" placeholder="Type message for generic comment reply">{{ $page->generic_comment_reply }}</textarea>
        </div>
    </div>
    <hr />
    <div class="form-group fieldGroup">
        <div class="input-group">
            <label class="font-bold">Generic private message</label>
            <textarea style="width: 90%" class="form-control" name="generic_private_reply" placeholder="Type message for generic private message">{{ $page->generic_private_reply }}</textarea>
        </div>
    </div>
    <hr />
    @foreach($replies as $reply)
        <div class="form-group fieldGroup">
            <div class="input-group">
                <label class="font-bold">Filter words</label>
                <input style="width: 90%" type="text" name="filterWords[]" class="form-control" value="{{ $reply->filter_words }}"/><br />
                <div class="input-group-addon">
                    <a href="javascript:void(0)" class="btn btn-danger remove"><i class="fa fa-trash"></i></a>
                </div>
                <label class="font-bold">Auto-reply comment</label>
                <textarea style="width: 90%" class="form-control" name="commentBody[]" placeholder="Type message for auto reply">{{ $reply->comment_body }}</textarea>
                <label class="font-bold">Private message</label>
                <textarea style="width: 90%" class="form-control" name="privateMessage[]" placeholder="Type message for private message">{{ $reply->private_message }}</textarea>
            </div>
        </div>
    @endforeach
    <div class="form-group fieldGroup">
        <div class="input-group">
            <input style="width: 90%" type="text" name="filterWords[]" class="form-control" placeholder="Type words (comma separated)"/><br />
            <div class="input-group-addon">
                <a href="javascript:void(0)" class="btn btn-success addMore"><i class="fa fa-plus"></i></a>
            </div>
            <textarea style="width: 90%" class="form-control" name="commentBody[]" placeholder="Type message for auto reply"></textarea>
            <textarea style="width: 90%" class="form-control" name="privateMessage[]" placeholder="Type message for private message"></textarea>
        </div>
    </div>

    <div class="form-group fieldGroupCopy" style="display: none;">
        <div class="input-group">
            <input style="width: 90%" type="text" name="filterWords[]" class="form-control" placeholder="Type words (comma separated)"/><br />
            <div class="input-group-addon">
                <a href="javascript:void(0)" class="btn btn-danger remove"><i class="fa fa-trash"></i></a>
            </div>
            <textarea style="width: 90%" class="form-control" name="commentBody[]" placeholder="Type message for auto reply"></textarea>
            <textarea style="width: 90%" class="form-control" name="privateMessage[]" placeholder="Type message for private message"></textarea>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function(){
        let maxGroup = 10;
        $(".addMore").click(function(){
            if($('body').find('.fieldGroup').length < maxGroup){
                let fieldHTML = '<div class="form-group fieldGroup">'+$(".fieldGroupCopy").html()+'</div>';
                $('body').find('.fieldGroup:last').after(fieldHTML);
            } else{
                alert('Maximum '+maxGroup+' replies can be added');
            }
        });

        $("body").on("click",".remove",function(){
            $(this).parents(".fieldGroup").remove();
        });
    });
</script>
