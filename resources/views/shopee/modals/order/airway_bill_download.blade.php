<x-modal.modal-large id="__modalOrderAirwayBill">
    <x-modal.header>
        <x-modal.title>
            Download
        </x-modal.title>
        <x-modal.close-button class="__btnCloseModalOrderAirwayBill" />
    </x-modal.header>
    <x-modal.body>
        <div class="w-full overflow-x-auto mb-10" id="div_download_pdf_for_bulk_orders">
            <div class="mb-2">
                <div class="grid grid-cols-1 gap-4 text-center">
                    <div class="pt-3">
                        <strong class="text-blue-500 pt-3">
                            Generate Downloadable Airway Bill Pdf
                        </strong>
                        <p id="download_pdf_for_bulk_orders_message" class="pt-4"></p>
                        <p class="pt-2">When the processing for pdf for bulk orders are going on you won't be able to request for new pdfs until they are complete.</p>
                    </div>

                    <div class="pb-3">
                        <x-button color="blue" class="btn-action--blue sm:w-1/4 mx-auto" id="generate_download_pdf_for_bulk_orders_btn">Confirm</x-button>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 pt-6 pb-6 text-center">
                    <strong class="text-blue-500 pt-2">Downloadable Airway Bill Pdf List</strong>
                </div>

                <div id="div_download_pdf_for_bulk_orders__list">
                </div>
            </div>
        </div>
    </x-modal.body>
</x-modal.modal-large>

@push('bottom_js')
<script>
    $(document).ready(function() {
        $("#generate_download_pdf_for_bulk_orders_btn").prop("disabled", true);
        deleteAllShopeeAirwayBillPdfs();
        checkIfPdfCanBeGenerated();
        getDowbloadableAirwayBillPdfs();
    });

    let shopee_airway_bill_timer;
    $("#btn_bulk_download_awb").on("click", function() {
        $("#download_pdf_for_bulk_orders_message").html("");
        $("#generate_download_pdf_for_bulk_orders_btn").prop("disabled", true);
        checkIfPdfCanBeGenerated();
        getDowbloadableAirwayBillPdfs();
        $('#__modalOrderAirwayBill').doModal('open');
        shopee_airway_bill_timer = setInterval(function() {
            checkIfPdfCanBeGenerated();
            getDowbloadableAirwayBillPdfs();
            $("#refresh_shopee_airway_bills_list_btn").removeClass("hide");
        }, 30000);
    });

    $('.__btnCloseModalOrderAirwayBill').on('click', function() {
        reloadOrderStatusListForShopee();
        shopeeOrderPurchaseTable.ajax.reload();
        $('#__modalOrderAirwayBill').doModal('close');
        clearInterval(shopee_airway_bill_timer);
    });

    $(document).on("click", "#generate_download_pdf_for_bulk_orders_btn", function() {
        generateInBulkPdf();
    });

    const generateInBulkPdf = () => {
        var rows_selected = shopeeOrderPurchaseTable.column(0).checkboxes.selected();

        var arr = [];
        $.each(rows_selected, function(index, row_id) {
            arr[index] = row_id;
        });

        if (arr.length === 0) {
            $("#download_pdf_for_bulk_orders_message").html("<p class='alert alert-danger'>Please Select At Least 1 Row</p>");
            return;
        }

        var json_data = JSON.stringify(arr);

        $.ajax({
            url: '{{ route("shopee.order.generate_airway_bill_in_bulk") }}',
            type: "POST",
            data: {
                'json_data': json_data,
                '_token': $('meta[name=csrf-token]').attr('content')
            },
            beforeSend: function() {
                $("#download_pdf_for_bulk_orders_message").html("<p class='alert alert-warning'>Processing</p>");
            }
        }).done(function(response) {
            if(response.success) {
                getDowbloadableAirwayBillPdfs();
                $("#download_pdf_for_bulk_orders_message").html("<p class='alert alert-success'>Successfully started generation of airway bill pdfs.</p>");
                setTimeout(function(){
                    $("#download_pdf_for_bulk_orders_message").html("");
                    let percentage = 0;
                    if (typeof(response.data.percentage) !== "undefined") {
                        percentage = response.data.percentage;
                    }
                    shopeeAirwayBillPdfIsGeneratingWithProgressBarHtml(percentage);
                    reloadOrderStatusListForShopee();
                }, 2000);
            } else {
                let message = "Failed to generate pdfs.";
                if (typeof(response.message) !== "undefined") {
                    message = response.message;
                }
                $("#download_pdf_for_bulk_orders_message").html("<p class='alert alert-danger'>"+message+"</p>");
            }
        });
    }

    const getDowbloadableAirwayBillPdfs = () => {
        $.ajax({
            url: '{{ route("shopee.order.get_downloadable_airway_bill") }}',
            type: "GET",
            data: {
                '_token': $('meta[name=csrf-token]').attr('content')
            },
            beforeSend: function() {
            }
        }).done(function(response) {
            if(response.success){
                let html = '';
                if (typeof(response.data) !== "undefined") {
                    if (response.data.length > 0) {
                        $.each(response.data, function(index, pdf_info) {
                            let url = "{{ route('shopee.order.download_airway_bill', ':token') }}";
                            url = url.replace(':token', pdf_info.token);
                            html += '<div id="downloadable_pdf_row__'+pdf_info.token+'" class="grid grid-cols-2 gap-4">';
                            html += '<div class="pb-3" style="word-wrap:break-word;">'+pdf_info.name+' ( '+pdf_info.date+' )</div>';
                            html += '<div class="text-center">';
                            html += '<a href="'+url+'" class="btn-action--green shopee_pdf_download_btn" data-token="'+pdf_info.token+'" target="_blank">Download Pdf</a>';
                            html += '</div>';
                            html += '</div>';
                            if (pdf_info.missing_orders.length > 0) {
                                html += '<div id="downloadable_pdf_row_missing_orders__'+pdf_info.token+'" class="grid grid-cols-1" >';
                                html += '<p style="font-size:14px;margin-bottom:0px;"><strong>Missing orders: '+pdf_info.missing_orders.split(",").length+'</strong></p>';
                                html += '<p style="word-break:break-all;font-size:12px;margin-bottom:0px;">'+pdf_info.missing_orders+'</p>';
                                html += '</div>';
                            }
                        });
                        $("#oaib_downloadable_pdf_count").html("("+response.data.length+")");
                    } else {
                        html += '<p class="text-center pt-3">Nothing found.</p>';
                    }
                    $("#div_download_pdf_for_bulk_orders__list").html(html);
                }
            }
        });
    }

    $(document).on("click", ".shopee_pdf_download_btn", function(el) {
        let token = $(this).data("token");
        $("#downloadable_pdf_row__"+token).remove();
        $("#downloadable_pdf_row_missing_orders__"+token).remove();
        var el_len = $('.shopee_pdf_download_btn').length;
        if (el_len === 0) {
            $("#div_download_pdf_for_bulk_orders__list").html('<p class="text-center pt-3">Nothing found.</p>');
            $("#oaib_downloadable_pdf_count").html("");
        } else {
            $("#oaib_downloadable_pdf_count").html("("+el_len+")");
        }

        setTimeout(function() {        
            deleteShopeeAirwayBillPdf(token);
        }, 5000);
    });

    const deleteShopeeAirwayBillPdf = (token) => {
        if (typeof(token) === "undefined" || token === "") {
            return;
        }
        $.ajax({
            url: '{{ route("shopee.order.delete_airway_bill") }}',
            type: "POST",
            data: {
                "token": token,
                '_token': $('meta[name=csrf-token]').attr('content')
            },
            beforeSend: function() {
            }
        }).done(function(response) {
            if (response.success) {
                $("#downloadable_pdf_row__"+token).remove();
            }
        });
    }

    const deleteAllShopeeAirwayBillPdfs = () => {
        $.ajax({
            url: '{{ route("shopee.order.delete_all_airway_bill") }}',
            type: "POST",
            data: {
                '_token': $('meta[name=csrf-token]').attr('content')
            }
        }).done(function(response) {
        });
    }

    const checkIfPdfCanBeGenerated = () => {
        $.ajax({
            url: '{{ route("shopee.order.can_generate_airway_bill") }}',
            type: "POST",
            data: {
                '_token': $('meta[name=csrf-token]').attr('content')
            }
        }).done(function(response) {
            if(response.success){
                if (typeof(response.data.can_generate) !== "undefined") {
                    if (response.data.can_generate) {
                        $("#generate_download_pdf_for_bulk_orders_btn").prop("disabled", false);
                        $("#download_pdf_for_bulk_orders_message").html("");
                    } else {
                        $("#generate_download_pdf_for_bulk_orders_btn").prop("disabled", true);
                        if (typeof(response.data.percentage) !== "undefined") {
                            shopeeAirwayBillPdfIsGeneratingWithProgressBarHtml(response.data.percentage);
                        } else {
                            shopeeAirwayBillPdfIsGeneratingWithSpinnerHtml();
                        }
                    }
                }
            }
        });
    }

    const shopeeAirwayBillPdfIsGeneratingWithProgressBarHtml = (percentage=0) => {
        let html = '<progress id="file" value="'+percentage+'" max="100" style="height:20px;"> '+percentage+'% </progress>';
        html += '<button id="refresh_shopee_airway_bills_list_btn" class="btn-action--green ml-2 hide"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-repeat" viewBox="0 0 16 16">';
        html += '<path d="M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41zm-11 2h3.932a.25.25 0 0 0 .192-.41L2.692 6.23a.25.25 0 0 0-.384 0L.342 8.59A.25.25 0 0 0 .534 9z"/>';
        html += '<path fill-rule="evenodd" d="M8 3c-1.552 0-2.94.707-3.857 1.818a.5.5 0 1 1-.771-.636A6.002 6.002 0 0 1 13.917 7H12.9A5.002 5.002 0 0 0 8 3zM3.1 9a5.002 5.002 0 0 0 8.757 2.182.5.5 0 1 1 .771.636A6.002 6.002 0 0 1 2.083 9H3.1z"/>';
        html += '</svg></button>';
        $("#download_pdf_for_bulk_orders_message").html(html);
        $("#generate_download_pdf_for_bulk_orders_btn").prop("disabled", true);
    }

    const shopeeAirwayBillPdfIsGeneratingWithSpinnerHtml = () => {
        var img = '{{ asset("img/spinner.svg") }}';
        $("#download_pdf_for_bulk_orders_message").html("<img class='' style='height:70px;' src='"+img+"' alt=''>");
        $("#generate_download_pdf_for_bulk_orders_btn").prop("disabled", true);
    }

    $(document).on("click", "#refresh_shopee_airway_bills_list_btn", function() {
        checkIfPdfCanBeGenerated();
        getDowbloadableAirwayBillPdfs();
    });
</script>
@endpush