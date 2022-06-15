<style type="text/css">
	.display_none{
		display: none;
	}
	.contact_add_wrapper{
		display: none;
	}
</style>
<div class="modal" tabindex="-1" role="dialog" id="customer_channel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title"><strong>{{ isset($editData) ? 'Edit Customer Info' : 'Add Customer Info' }}</strong></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="">

					<div class="form-group col-lg-12">
						<label for="email"><strong>Phone:</strong></label>
						<input type="text" name="contact_phone" class="form-control w-full rounded-md shadow-sm border-gray-300 contact_phone" placeholder="Phone" id="contact_phone" value="@if(isset($editData->customer->contact_phone)){{$editData->customer->contact_phone}}@endif" >
					</div>
				</div>

				<div class="col-lg-12 no_data_found display_none mb-3" style="text-align: center;">
					<button class="btn btn-danger btn-block">No Data Found</button>
				</div>

				<div class="contact_add_wrapper">

					<!-- <div style="text-align: left; margin-bottom: 15px; margin-left: 15px; margin-right: 5px;">
						<input id="current" type="radio" class="selection" name="selection" value="1">
						<label for="current">Select Current Customer</label>
						<input type="radio" class="selection" name="selection" value="2">
						Add New Customer
					</div> -->

					<div class="col-lg-12">
                        <input type="text" hidden name="customer_id" id="customer_id" value="@if(isset($editData) && isset($editData->customer_id)) {{$editData->customer_id}} @endif">
						<div class="form-group">
							<label for="email"><strong>Customer Name:</strong></label>
							<input type="text" name="customer_name" class="form-control w-full rounded-md shadow-sm border-gray-300 customer_name" placeholder="Customer Name" id="customer_name" value="@if(isset($editData)) {{$editData->customer_name}} @endif" >
						</div>
						<div class="form-group">
							<label for="email"><strong>Phone:</strong></label>
							<input type="text" name="customer_phone" class="form-control w-full rounded-md shadow-sm border-gray-300 contact_name" placeholder="phone" id="contact_phone_show">
						</div>
						<div class="form-group">
                            @foreach ($channels as $channel)
                                <input type="radio" name="channel" value="{{ $channel->name }}" id="channel_{{ $channel->id }}" @if(isset($editData) && $editData->channel_id == $channel->id ) checked='true' @endif><label for="{{ $channel->name }}" style="padding-left: 5px">{{ $channel->name }}</label>
                            @endforeach

{{--							<input type="radio" name="channel" value="2" id="Line" @if(isset($editData) && $editData->contact_name == 2) Checked='true' @endif><label for="Line" style="padding-left: 5px"> Line</label>--}}

{{--							<input type="radio" name="channel" value="3" id="Phone" style="margin-left: 10px" @if(isset($editData) && $editData->channel == 3) Checked='true' @endif><label for="Phone" style="padding-left: 5px"> Phone</label>--}}
						</div>
						<div class="form-group">
							<label for="email"><strong>Contact Name:</strong></label>
							<input type="text" name="contact_name"   class="form-control w-full rounded-md shadow-sm border-gray-300 contact_name" placeholder="Contact Name" id="contact_name" value="@if(isset($editData)) {{$editData->contact_name}} @endif" >
						</div>
					</div>
				</div>

			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary add_customer_channel">Save changes</button>
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">

    $(document).ready(function() {

        $(".contact_phone").keypress(function(e) {
            //var currentUrl = window.location.origin;
            if(e.which == 13) {
                if($(this).val() !== "" )
                {
                    var customer_phone = $(this).val();
                    $.ajax
                    ({
                        type: 'GET',
                        data: {customer_phone:$(this).val()},
                        url: '{{url('check_customer_phone')}}',
                        success: function(result)
                        {
                            console.log(result);
                            console.log(result.customer_name);
                            if(result === "0"){
                                $(".no_data_found").show();
                                $(".customer_info").hide();
                                $(".contact_add_wrapper").show();

                                $("#customer_name").val(null);
                                $("#contact_phone_show").val(null);
                                $("#contact_phone_show").val(customer_phone);
                                $('#contact_phone_show').prop('readonly', true);
                                $("#contact_name").val(null);
                                $("#customer_id").val(0);
                                $('#customer_name').prop('readonly', false);
                                
                            }

                            if(result.customer_name){
                                console.log(result.contact_name);
                                $(".no_data_found").hide();
                                $("#customer_name").text(result.customer_name);
                                // $("#customer_channel_div").val(result.channel);
                                $("#channel_"+result.channel).prop("checked", true);
                                $("#customer_name_input").val(result.customer_name);
                                $(".contact_add_wrapper").show();

                                $("#customer_name").val(result.customer_name);
                                $("#contact_phone_show").val(customer_phone);
                                $("#contact_name").val(result.contact_name);
                                $("#customer_id").val(result.customer_id);

                                $('#customer_name').prop('readonly', true);
                                $('#contact_phone_show').prop('readonly', true);
                            }
                        }
                    });
                }
            }
        })

        $(".add_customer_channel").click(function(e) {

            var customer_id =  $("#customer_id").val();
            var contact_phone = $("#contact_phone").val();
            var customer_name = $("#customer_name").val();
            var channel =  $("input[type='radio'][name='channel']:checked").val();

            if(!channel){
                var channel_data = '';
            }
            else{
                var channel_data = channel;
            }
            var contact_name = $("#contact_name").val();
            if(contact_phone.length === 0){
                alert("Please fill up phone No");
                return false;
            }

            if(customer_name.length === 0){
                alert("Please fill up Name");
                return false;
            }

            if(!channel){
                alert("Please select any of the channel");
                return false;
            }

            if(contact_name.length === 0){
                alert("Please fill up Contact Name");
                return false;
            }

            $("#contact_phone_div").text('Contact Phone : '+contact_phone);
            $("#customer_name_div").text('Customer Name : '+customer_name);

            $("#contact_channel_div").text('Channel : ' +channel_data);

            // if(channel === '1'){
            //     $("#contact_channel_div").text('Channel : Facebook');
            // }
            // if(channel === '2'){
            //     $("#contact_channel_div").text('Channel : Line');
            // }
            // if(channel === '3'){
            //     $("#contact_channel_div").text('Channel : Phone');
            // }

            $("#contact_name_div").text('Contact Name : ' + contact_name);

            $("#customer_id_main").val(customer_id);
            $("#customer_name_main").val(customer_name);
            $("#contact_phone_main").val(contact_phone);
            $("#channel_main").val(channel_data);
            $("#contact_name_main").val(contact_name);

            $("#customer_channel").modal('hide');
            $("#no_selected_customers").hide();
        })
    });
</script>
