

<link rel="stylesheet" href="{{ asset('css/magicscroll/magicscroll.css') }}">
<!-- Scripts -->
<script src="{{ asset('js/magicscroll/magicscroll.js') }}"></script>
<script>
	var callbacks = {
		onReady: function () {
			console.log('onReady', arguments[0]);
		},
		onStop: function () {
			console.log('onStop', arguments[0]);
		},
		onItemHover: function () {
			console.log('onItemHover', arguments[0]);
		},
		onItemOut: function () {
			console.log('onItemOut', arguments[0]);
		},
		onMoveStart: function () {
			console.log('onMoveStart', arguments[0]);
		},
		onMoveEnd: function () {
			console.log('onMoveEnd', arguments[0]);
		}
	};

	var MagicScrollOptions = {};
	magicJS.extend(MagicScrollOptions, callbacks);

	function isDefaultOption(o) {
		return magicJS.$A(magicJS.$(o).byTag('option')).filter(function(opt){
			return opt.selected && opt.defaultSelected;
		}).length > 0;
	}

	function toOptionValue(v) {
		if ( /^(true|false)$/.test(v) ) {
			return 'true' === v;
		}
		if ( /^[0-9]{1,}$/i.test(v) ) {
			return parseInt(v,10);
		}
		return v;
	}

	function makeOptions(optType) {
		var  value = null, isDefault = true, newParams = Array(), newParamsS = '', options = {};
		magicJS.$(magicJS.$A(magicJS.$(optType).getElementsByTagName("INPUT"))
			.concat(magicJS.$A(magicJS.$(optType).getElementsByTagName('SELECT'))))
		.forEach(function(param){
			value = ('checkbox'==param.type) ? param.checked.toString() : param.value;

			isDefault = ('checkbox'==param.type) ? value == param.defaultChecked.toString() :
			('SELECT'==param.tagName) ? isDefaultOption(param) : value == param.defaultValue;

			if ( null !== value && !isDefault) {
				options[param.name] = toOptionValue(value);
			}
		});

		magicJS.extend(options, callbacks);
		return options;
	}

	function updateScriptCode() {
		var code = '&lt;script&gt;\nvar MagicScrollOptions = ';
		code += JSON.stringify(MagicScrollOptions, null, 2).replace(/\"(\w+)\":/g,"$1:")+';';
		code += '\n&lt;/script&gt;';

		magicJS.$('app-code-sample-script').changeContent(code);
	}

	function updateInlineCode() {
		var code = '&lt;div class="MagicScroll" data-options="';
		code += JSON.stringify(MagicScrollOptions).replace(/\"(\w+)\":(?:\"([^"]+)\"|([^,}]+))(,)?/g, "$1: $2$3; ").replace(/\{([^{}]*)\}/,"$1").replace(/\s*$/,'');
		code += '"&gt;';

		magicJS.$('app-code-sample-inline').changeContent(code);
	}

	function applySettings() {
		MagicScroll.stop('scroll-1');
		MagicScrollOptions = makeOptions('params');
		MagicScroll.start('scroll-1');
		updateScriptCode();
		updateInlineCode();
		try {
			prettyPrint();
		} catch(e) {}
	}

	function copyToClipboard(src) {
		var
		copyNode,
		range, success;

		if (!isCopySupported()) {
			disableCopy();
			return;
		}
		copyNode = document.getElementById('code-to-copy');
		copyNode.innerHTML = document.getElementById(src).innerHTML;

		range = document.createRange();
		range.selectNode(copyNode);
		window.getSelection().addRange(range);

		try {
			success = document.execCommand('copy');
		} catch(err) {
			success = false;
		}
		window.getSelection().removeAllRanges();
		if (!success) {
			disableCopy();
		} else {
			new magicJS.Message('Settings code copied to clipboard.', 3000,
				document.querySelector('.app-code-holder'), 'copy-msg');
		}
	}

	function disableCopy() {
		magicJS.$A(document.querySelectorAll('.cfg-btn-copy')).forEach(function(node) {
			node.disabled = true;
		});
		new magicJS.Message('Sorry, cannot copy settings code to clipboard. Please select and copy code manually.', 3000,
			document.querySelector('.app-code-holder'), 'copy-msg copy-msg-failed');
	}

	function isCopySupported() {
		if ( !window.getSelection || !document.createRange || !document.queryCommandSupported ) { return false; }
		return document.queryCommandSupported('copy');
	}
</script>
<style type="text/css">
	.cfg-btn {
		background-color: rgb(55, 181, 114);
		color: #fff;
		border: 0;
		box-shadow: 0 0 1px 0px rgba(0,0,0,0.3);
		outline:0;
		cursor: pointer;
		width: 200px;
		padding: 10px;
		font-size: 1em;
		position: relative;
		display: inline-block;
		margin: 10px auto;
	}
	.cfg-btn:hover:not([disabled]) {
		background-color: rgb(37, 215, 120);
	}
	.mobile-magic .cfg-btn:hover:not([disabled]) { background: rgb(55, 181, 114); }
	.cfg-btn[disabled] { opacity: .5; color: #808080; background: #ddd; }

	.cfg-btn.btn-preview,
	.cfg-btn.btn-preview:active,
	.cfg-btn.btn-preview:focus {
		font-size: 1em;
		position: relative;
		display: block;
		margin: 10px auto;
	}

	.cfg-btn,
	.preview,
	.app-figure,
	.api-controls,
	.wizard-settings,
	.wizard-settings .inner,
	.wizard-settings .footer,
	.wizard-settings input,
	.wizard-settings select {
		-webkit-box-sizing: border-box;
		-moz-box-sizing: border-box;
		box-sizing: border-box;
	}
	.preview,
	.wizard-settings {
		padding: 10px;
		border: 0;
		min-height: 1px;
	}
	.preview {
		position: relative;
	}

	.api-controls {
		text-align: center;
	}
	.api-controls button,
	.api-controls button:active,
	.api-controls button:focus {
		width: 80px; font-size: .7em;
		white-space: nowrap;
	}

	.app-figure {
		width: 100% !important;
		/* margin: 50px auto; border: 0px solid red;*/
		padding: 10px;
		position: relative;
		text-align: center;
	}
	.selectors { margin-top: 10px; }

	.app-code-sample {
		max-width: 80%;
		/*  margin: 80px auto 0;*/
		text-align: center;
		position: relative;
	}
	.app-code-sample input[type="radio"] {
		display: none;
	}
	.app-code-sample label {
		display: inline-block;
		padding: 2px 12px;
		margin: 0;
		font-size: .8em;
		text-decoration: none;
		cursor: pointer;
		color: #666;
		border: 1px solid rgba(136, 136, 136, 0.5);
		background-color: transparent;
	}
	.app-code-sample label:hover {
		color: #fff;
		background-color: rgb(253, 154, 30);
		border-color: rgb(253, 154, 30);
	}
	.app-code-sample input[type="radio"]:checked+label {
		color: #fff;
		background-color: rgb(110, 110, 110) !important;
		border-color: rgba(110, 110, 110, 0.7) !important;
	}
	.app-code-sample label:first-of-type {
		border-radius: 4px 0 0 4px;
		border-right-color: transparent;
	}
	.app-code-sample label:last-of-type {
		border-radius: 0 4px 4px 0;
		border-left-color: transparent;
	}

	.app-code-sample .app-code-holder {
		padding: 0;
		position: relative;
		border: 1px solid #eee;
		border-radius: 0px;
		background-color: #fafafa;
		margin: 15px 0;
	}
	.app-code-sample .app-code-holder > div {
		display: none;
	}
	.app-code-sample .app-code-holder pre {
		text-align: left;
		white-space: pre-line;
		border: 0px solid #eee;
		border-radius: 0px;
		background-color: transparent;
		padding: 25px 50px 25px 25px;
		margin: 0;
		min-height: 25px;
	}
	.app-code-sample input[type="radio"]:nth-of-type(1):checked ~ .app-code-holder > div:nth-of-type(1) {
		display: block;
	}
	.app-code-sample input[type="radio"]:nth-of-type(2):checked ~ .app-code-holder > div:nth-of-type(2) {
		display: block;
	}

	.app-code-sample .app-code-holder .cfg-btn-copy {
		display: none;
		z-index: -1;
		position: absolute;
		top:10px; right: 10px;
		width: 44px;
		font-size: .65em;
		white-space: nowrap;
		margin: 0;
		padding: 4px;
	}
	.copy-msg {
		font: normal 11px/1.2em 'Helvetica Neue', Helvetica, 'Lucida Grande', 'Lucida Sans Unicode', Verdana, Arial, sans-serif;
		color: #2a4d14;
		border: 1px solid #2a4d14;
		border-radius: 4px;
		position: absolute;
		top: 8px;
		left: 0;
		right: 0;
		width: 200px;
		max-width: 70%;
		padding: 4px;
		margin: 0px auto;
		text-align: center;
	}
	.copy-msg-failed {
		color: #b80c09;
		border-color: #b80c09;
		width: 430px;
	}
	.mobile-magic .app-code-sample .cfg-btn-copy { display: none; }
	#code-to-copy { position: absolute; width: 0; height: 0; top: -10000px; }
	.lt-ie9-magic .app-code-sample { display: none; }

	.wizard-settings {
		background-color: #4f4f4f;
		color: #a5a5a5;
		position: absolute;
		right: 0;
		width: 340px;
	}
	.wizard-settings .inner {
		width: 100%;
		margin-bottom: 30px;
	}
	.wizard-settings .footer {
		color: #c7d59f;
		font-size: .75em;
		width: 100%;
		position: relative;
		vertical-align: bottom;
		text-align: center;
		padding: 6px;
		margin-top: 10px;
	}
	.wizard-settings .footer a { color: inherit; text-decoration: none; }
	.wizard-settings .footer a:hover { text-decoration: underline; }

	.wizard-settings a { color: #cc9933; }
	.wizard-settings a:hover { color: #dfb363; }
	.wizard-settings table > tbody > tr > td { vertical-align: top; }
	.wizard-settings table { min-width: 300px; max-width: 100%; font-size: .8em; margin: 0 auto; }
	.wizard-settings table caption { font-size: 1.5em; padding: 16px 8px; }
	.wizard-settings table td { padding: 4px 8px; }
	.wizard-settings table td:first-child { white-space: nowrap; }
	.wizard-settings table td:nth-child(2) { text-align: left; }
	.wizard-settings table td .values {
		color: #a08794;
		font-size: 0.8em;
		line-height: 1.3em;
		display: block;
		max-width: 170px;
	}
	.wizard-settings table td .values:before { content: ''; display: block; }

	.wizard-settings input,
	.wizard-settings select {
		width: 170px;
	}
	.wizard-settings input {
		padding: 0px 4px;
	}
	.wizard-settings input[disabled] {
		color: #808080;
		background: #a7a7a7;
		border: 1px solid #a7a7a7;
	}

	.preview { width: 70%; float: left; }
	@media (min-width: 0px) {
		.preview { width: 100%; float: none; }
	}
	@media (min-width: 1024px) {
		.preview { width: calc(100% - 340px); }
		.wizard-settings { top: 0; min-height: 100%; }
		.wizard-settings .inner { margin-top: 60px; }
		.wizard-settings .footer { position: absolute; bottom: 0; left: 0; }
		.wizard-settings .settings-controls {
			position: fixed;
			top: 0; right: 0;
			width: 340px;
			padding: 10px 0 0;
			text-align: center;
			background-color: inherit;
		}
	}
	@media screen and (max-width: 1023px) {
		.app-figure { width: 98% !important;  }
		.app-code-sample { display: none; }
		.wizard-settings { width: 100%; }
	}
	@media screen and (max-width: 600px) {
		.api-controls button, .api-controls button:active, .api-controls button:focus {
			width: 70px;
		}
	}
	@media screen and (max-width: 560px) {
		.api-controls .sep { content: ''; display: table; }
	}
	@media screen and (min-width: 1600px) {
		.preview { padding: 10px 160px; }
	}
</style>
<style type="text/css">
	.display_none{
		display: none;
	}
	.contact_add_wrapper{
		display: none;
	}

	@media only screen and (max-width: 500px) {
		.grid_select{
			width: 48%;
			float: left;
		}

		.product_ind{
			width: 24%;
			float: left;
			margin-right: 3px;
		}
	}
</style>
<div class="modal" tabindex="-1" role="dialog" id="grid_product">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title"><strong>Select Product</strong></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="row">

					<div class="form-group col-lg-12 col-sm-12 col-xs-12 grid_select">
						<label for="category"><strong>Select Category:</strong></label>
						<select name="category" id="category" class="form-control" style="width: 100%;">
							@foreach ($categories as $category)
							<option value="{{ $category->id }}"> {{ $category->cat_name }} </option>
							@endforeach
						</select>
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
							@include('layouts.magic_scroller_css')
						</div>
					</div>
				</div> -->

				<div class="sub_category_image">
					<table class="table-auto border-collapse w-100  border mt-4" id="yajra_datatable">
						<thead class="border bg-green-300">
							<tr class="rounded-lg text-sm font-medium text-gray-700 text-left">
								<th class="px-4 py-2 text-center"></th>
								<th class="px-4 py-2 text-center">Image</th>
								<th class="px-4 py-2 text-center">Sub Category</th>
								<th class="px-4 py-2 text-center">Manage</th>
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
				</div>

				<div class="row products_grid">

				</div>

			</div>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-primary" onclick="add_customer_channel();">Save changes</button>
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

		$('#category').change(function(){
			var categoryID = $(this).val();  
			if(categoryID){
				$.ajax({
					type:"GET",
					data: {categoryID:$(this).val()},
					url:"{{url('get-Sub-Catgeory')}}",
					success:function(res){   
						console.log(res);    
						if(res){
							
							// $("#sub-category").empty();
							// $("#sub-category").append('<option>Select Sub Category</option>');
							// $.each(res,function(key,value){
							// 	$("#sub-category").append('<option value="'+value+'">'+key+'</option>');
							// });
							$(".MagicScroll").empty();
							$(".MagicScroll").html(res);
							

						}else{
							$("#sub-category").empty();
						}
					}
				});
			}else{
				$("#sub-category").empty();
				$("#sub-category").empty();
			}   
		});


		$('#sub-category').change(function(){
			var categoryID = $(this).val(); 

			if(categoryID){
				$.ajax({
					type:"GET",
					data: {categoryID:$(this).val()},
					url:"{{url('get-all-pro-Sub-Catgeory')}}",
					success:function(res){ 
					//alert(res);      
					if(res){
						$(".products_grid").empty();
						$(".products_grid").html(res);

					}else{
						$(".products_grid").empty();
					}
				}
			});
			}else{
				$(".products_grid").empty();
			}   
		});

	});

	// for ajax pagination

	$(function() {
		$('body').on('click', '.pagination a', function(e) {
			e.preventDefault();

			$('#load a').css('color', '#dfecf6');
			$('#load').append('<img style="position: absolute; left: 0; top: 0; z-index: 100000;" src="/images/loading.gif" />');

			var url = $(this).attr('href');  
			getArticles(url);
        //window.history.pushState("", "", url);
    });

		function getArticles(url) {
			$.ajax({
				url : url  
			}).done(function (data) {
				$('.products_grid').html(data);  
			}).fail(function () {
				alert('Articles could not be loaded.');
			});
		}
	});


	 $(document).ready(function() {

        dataTables("{{ route('ordersList') }}");
        var datatable;

        $('.btn_processing').click(function() {
            datatable.destroy();
            var status = 'open';
            dataTables("{{ route('ordersList') }}?status=" + status);
            $(".btn_process_order").removeClass('hide');
        });

        $('.filter_status li').click(function() {
            datatable.destroy();
            $('.filter_status li.active').removeClass('active');
            $(this).addClass("active");
            var status = $(this).data("status");

            dataTables("{{ route('ordersList') }}?status=" + status);
        });

        function dataTables(url) {
            // Datatable
            datatable = $('#yajra_datatable').DataTable({
                processing: true,
                // responsive: true,
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
                    {
                        name: 'checkbox',
                        data: 'checkbox'
                    },
                    {data: 'id', name: 'id'},
                    {data: 'contact_name', name: 'contact_name'},
                    {data: 'channel', name: 'channel'},
           
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
</script>

