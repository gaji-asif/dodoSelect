<style type="text/css">
    .district_new, .sub_district_new, .province_new, .postcode_new{
        display: none;
    }
    .reset_div{
        display: none;
    }
    .reset_nutton{
        border-radius: 5px;
    }
</style>

<div class="modal" tabindex="-1" role="dialog" id="shipping_address_modal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><strong>Add Customer Info and Address</strong></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="form-group col-lg-6 col-6">
                        <label for="email"><strong>Name:</strong></label>
                        <input type="text" name="shipping_name"   class="form-control w-full rounded-md shadow-sm border-gray-300" placeholder="Name" id="shipping_name"
                               value="@if(isset($editData)){{$editData->shipping_name}}@endif" >
                    </div>
                    <div class="form-group col-lg-6 col-6">
                        <label for="email"><strong>Phone:</strong></label>
                        <input type="text" name="shipping_phone"   class="form-control w-full rounded-md shadow-sm border-gray-300" placeholder="Phone" id="shipping_phone" required="required"
                               value="@if(isset($editData)){{$editData->shipping_phone}}@endif" >
                    </div>

                    <div class="form-group col-lg-12">
                        <label for="email"><strong>Address:</strong></label>
                        <input type="text" name="shipping_address" class="form-control" placeholder="Address" id="shipping_address"
                               value="@if(isset($editData)){{$editData->shipping_address}}@endif">
                    </div>
                </div>

                <a style="float: right; background-color: #dc3545; padding: 2px 10px; color: #FFFFFF; cursor: pointer;" class="pull-right reset_nutton" onclick="reset_address();">Reset Address</a>

                <div class="main_div">

                    <div class="form-group">
                        <label for="email" style="width: 100%;"><strong style="float: left;">District</strong></label>
                        <div id="the-basicss">
                            <input class="typeaheads district form-control" id="district" type="text" placeholder="District of Thailand"
                                   value="@if(isset($editData->shipping_district)){{$editData->shipping_district}} @endif">
                            <input class="typeaheads district_new form-control" id="district_new" type="text" placeholder="District of Thailand">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email" style="width: 100%;"><strong style="float: left;">Sub District</strong></label>
                        <div id="the-basicss1">
                            <input class="typeaheads sub_district form-control" id="sub_district" type="text" placeholder="Sub District of Thailand" value="@if(isset($editData->shipping_sub_district)){{$editData->shipping_sub_district}} @endif">
                            <input class="typeaheads sub_district_new form-control" id="sub_district_new" type="text" placeholder="Sub District of Thailand">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email" style="width: 100%;"><strong style="float: left;">Province</strong></label>
                        <div id="the-basicss2">
                            <input class="typeaheads province form-control" id="province" type="text" placeholder="Province of Thailand" value="@if(isset($editData->shipping_province)){{$editData->shipping_province}}@endif">
                            <input class="typeaheads province_new form-control" id="province_new" type="text" placeholder="Province of Thailand">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email" style="width: 100%;"><strong style="float: left;">Postcode</strong></label>
                        <div id="the-basicss3">
                            <input class="typeaheads postcode form-control" id="postcode" type="text" placeholder="Postcode of Thailand"
                                   value="@if(isset($editData->shipping_postcode)){{$editData->shipping_postcode}}@endif">
                            <input class="typeaheads postcode_new form-control" id="postcode_new" type="text" placeholder="Postcode of Thailand">
                        </div>
                    </div>
                </div>

                @include('layouts.thailand_address')

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="save_address()">Save changes</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
