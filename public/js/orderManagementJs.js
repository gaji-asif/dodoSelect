// $(document).on('click', '#add_another_shipment', function() {
// 	$("#__formCreateShipmentAnother").show();
// 	$("#__formCreateShipment").hide();

// 	let orderId = el.getAttribute('data-id');
//         $('#__order_idCreateShipment').val(orderId);
//         $('#__order_id_displayCreateShipment').val(`#${orderId}`);

//         //$('#__modalCreateShipment').doModal('open');
//         $.ajax({
//             type: 'GET',
//             url: '{{url('getAllOrderedPro')}}',
//             data: {orderId:orderId},
//             beforeSend: function() {
//              $("#all_ordered_products").html("Loading...");
//             },
//             success: function(responseData) {
//                 $("#all_ordered_products").html("");
//                 $("#all_ordered_products").html(responseData);
//             },
//             error: function(error) {
                
//             }
//         });
// });