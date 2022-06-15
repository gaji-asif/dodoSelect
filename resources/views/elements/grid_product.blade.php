 {{-- <link href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" rel="stylesheet">
 <link href="https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap4.min.css" rel="stylesheet">
 <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
 <script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>
 <link rel="stylesheet" href="{{ asset('css/custom_css.css') }}"> --}}

 <style type="text/css">
 	.display_none {
 		display: none;
 	}
 	.contact_add_wrapper {
 		display: none;
 	}

 	.add_product_to_cart {
 		padding-top: 10px;
 		margin-top: 10px;
 	}

 	#yajra_datatable tbody tr td {
 		text-align: center;
 	}

 	#yajra_datatable_product tbody tr td {
 		text-align: left;
 	}

 /*	table.dataTable tbody tr td img{
 		text-align: center;
 	}*/

 	@media only screen and (max-width: 500px) {
 		.grid_select {
 			width: 100%;
 			float: left;
 		}

 		.product_ind {
 			width: 24%;
 			float: left;
 			margin-right: 3px;
 		}

 		div.dataTables_wrapper div.dataTables_length label {
 			margin-bottom: -14px;
 		}
 	}
 </style>
 <div class="modal" tabindex="-1" role="dialog" id="grid_product">
 	<div class="modal-dialog" role="document">
 		<div class="modal-content">
 			<!-- <div class="modal-header">

 				<h5 class="modal-title"><strong>Select Category</strong></h5>
 				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
 					<span aria-hidden="true">&times;</span>
 				</button>
 			</div> -->
 			<div class="modal-body">
 				<div class="row">

 					<div class="col-lg-12">
 					<div style="float: left; width: 92%; margin-right: 1%;">
 						<div class="form-group grid_select">
 						<!-- <label for="category"><strong>Select Category:</strong></label> -->
 						<select name="category" id="category" class="form-control" style="width: 100%;">
 							<option value=""> -- All Categories -- </option>
 							@foreach ($categories as $category)
 							<option value="{{ $category->id }}"> {{ $category->cat_name }} </option>
 							@endforeach
 						</select>
 					</div>
 					</div>
 					<div style="float: right; width: 3%;">
 						<button style="float: right;" type="button" class="close" data-dismiss="modal" aria-label="Close">
 					<span style="font-weight: bold;" aria-hidden="true">&times;</span>
 				</button>
 					</div>

 				</div>

					<!-- <div class="form-group col-lg-6 col-sm-6 col-xs-6 grid_select">

						<label for="info1 mb-0">Sub category</label>


						<select class="form-control sub-category form-select sub-category" name="sub-category" id="sub-category">
						</select>
					</div> -->
				</div>

				<!-- <div class="row sub_category_image" id="sub_category_image">
					<div class="preview col preview_sub_cat">
						<div class="MagicScroll" id="scroll-1">

						</div>
					</div>
				</div> -->

				<div class="sub_category_image">
					<!-- <h5 class="modal-title"><strong>Sub Category Lists</strong></h5> -->
					<table class="table-auto border-collapse w-100  border mt-4" id="yajra_datatable">
						<thead class="border bg-green-300">
							<tr class="rounded-lg text-sm font-medium text-gray-700 text-left">
								<th class="px-4 py-2 text-center">Image</th>
								<th class="px-4 py-2 text-center">Sub Cat name</th>
								<th class="px-4 py-2 text-center">Manage</th>
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
				</div>

				<div class="row">
					<div class="products_grid display_none">
						 <h5 class="sub_cat_name">
						 	<strong class="sub_cat">Sub cat name </strong><br>
						 		<a class="back_to_list"> (Back to list)</a>

						 </h5>
						<table class="table-auto border-collapse w-100  border mt-4" id="yajra_datatable_product">
							<thead class="border bg-green-300">
								<tr class="rounded-lg text-sm font-medium text-gray-700 text-left">

									<th class="px-4 py-2">Product Lists</th>
									<th class="px-4 py-2"></th>
								</tr>
							</thead>
							<tbody>
							</tbody>
						</table>
					</div>
				</div>

			</div>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-primary" onclick="add_customer_channel();">Save changes</button>
			<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
		</div>
	</div>
</div>
{{-- </div> --}}

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
							console.log(result.contact_name);
							if(result === "0"){
								$(".no_data_found").show();
								$(".customer_info").hide();
								$(".contact_add_wrapper").show();

							}

							if(result.contact_name){

								$(".customer_info").show();
								$(".no_data_found").hide();
								$("#customer_name").text(result.contact_name);
								$("#customer_channel_div").text(result.channel);
								$("#customer_name_input").val(result.contact_name);
								$(".contact_add_wrapper").show();
							}

						}
					});

				}


			}
		})




		// $('#sub-category').change(function(){
		// 	var categoryID = $(this).val();

		// 	if(categoryID){
		// 		$.ajax({
		// 			type:"GET",
		// 			data: {categoryID:$(this).val()},
		// 			url:"{{url('get-all-pro-Sub-Catgeory')}}",
		// 			success:function(res){
		// 			//alert(res);
		// 			if(res){
		// 				$(".products_grid").empty();
		// 				$(".products_grid").html(res);

		// 			}else{
		// 				$(".products_grid").empty();
		// 			}
		// 		}
		// 	});
		// 	}else{
		// 		$(".products_grid").empty();
		// 	}
		// });

	});




	$(document).ready(function() {

		dataTables("{{ route('getAllSubCatgeory') }}");
		var datatable;


		$('#category').change(function(){
			var categoryID = $(this).val();
			$(".sub_category_image").show();
			$(".products_grid").hide();
			datatable.destroy();
			//alert(categoryID);
			if(categoryID){

				dataTables("{{ route('getAllSubCatgeory') }}?categoryID=" + categoryID);

			}
			else{
				dataTables("{{ route('getAllSubCatgeory') }}");
			}
		});

		$(document).on('click', '.back_to_list', function() {
			$('#category option:not(:selected)').attr('disabled', false);
		    $('#category').attr('readonly', false);
			var categoryID = $("#category").val();
			$(".sub_category_image").show();
			$(".products_grid").hide();
			datatable.destroy();
			//alert(categoryID);
			if(categoryID){

				dataTables("{{ route('getAllSubCatgeory') }}?categoryID=" + categoryID);

			}
			else{
				dataTables("{{ route('getAllSubCatgeory') }}");
			}
		});

		function dataTables(url) {
            // Datatable
            datatable = $('#yajra_datatable').DataTable({
            	processing: true,
                // responsive: true,
                lengthChange: false,
                serverSide: true,
                columnDefs : [
                {
                	'targets': 0,
                	'checkboxes': {
                		'selectRow': true
                	}
                }
                ],
                order: [[1, 'asc']],
                ajax: url,
                columns: [


                {data: 'image', name: 'image'},
                {data: 'cat_name', name: 'cat_name'},
                {
                	data: 'action',
                	name: 'action',
                	orderable: true,
                	searchable: true
                },
                ]
            });
        }
    });



	$(document).on('click', '.subCat', function() {

		var subCategoryID = $(this).data('id');
		//datatable.destroy();
		$(".display_none").show();
		$('#category option:not(:selected)').attr('disabled', true);
		$('#category').attr('readonly', true);

		$.ajax
			({
				type: 'GET',
				data: {subCategoryID:$(this).data('id')},
				url: '{{url('getSubCatName')}}',
				success: function(result)
				{
					$('.sub_cat').text(result.cat_name);

				}
			});

		$(".sub_category_image").hide();
		$('#yajra_datatable_product').DataTable().clear().destroy();
			//alert(categoryID);
			if(subCategoryID){

				dataTables_products("{{ route('getAllProductCatWise') }}?subCategoryID=" + subCategoryID);

			}
		});

	function dataTables_products(url) {
            // Datatable
            datatable = $('#yajra_datatable_product').DataTable({
            	processing: true,
                // responsive: true,
                lengthChange: false,
                serverSide: true,
                columnDefs : [
                {
                	'targets': 0,
                	'checkboxes': {
                		'selectRow': true
                	}
                }
                ],
                order: [[1, 'asc']],
                ajax: url,
                columns: [

                {data: 'product_details', name: 'product_details'},
                {
                	data: 'action',
                	name: 'action',
                	orderable: true,
                	searchable: true
                },
                ]
            });
        }




</script>

