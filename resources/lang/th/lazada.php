<?php

return [
    'lazada' => 'Lazada',
    'name' => 'Name',
    'email' => 'Email',
    'phone' => 'Phone',
    'price' => 'Price',
    'quantity' => 'Quantity',
    'total_price' => 'Total Price',
    'all' => "All",
    'open' => 'Open',
    'load' => 'Load',
    'close' => 'Close',
    'processing' => 'Processing',
    'top_nav' => [
        'dodo_orders' => 'Dodo Orders',
        'dropshipper_orders' => 'Dropshippers Orders',
        'status_tab' => [
            'to_pay' => 'To Pay',
            'to_process' => 'To Process',
            'shipping' => 'Shipping',
            'cancelled' => 'Cancelled',
            'completed' => 'Completed'
        ],
        'lazada_order_statuses' => [
            'unpaid' => 'Unpaid',
            'invoice_pending' => 'Invoice Pending',
            'ready_to_ship' => 'Ready to Ship',
            'to_confirm_receive' => 'To Confirm Receive',
            'to_return' => 'To Return',
            'retry_ship' => 'Retry Ship',
            'shipped' => 'Shipped',
            'cancelled' => 'Cancelled',
            'in_cancel' => 'In Cancel',
            'completed' => 'Completed'
        ],
    ],
    'product' => [
        'deleted' => 'Product has been deleted successfully',
        'not_found' => 'Product not found',
        'updated' => 'Product has been updated successfully!'
    ],
    'shop' => [
        'down' => 'Requested shop maybe down or invalid? Please try again...',
        'deleted' => 'Shop has been deleted successfully!',
        'updated' => 'Shop has been updated successfully!',
        'added' => 'Shop has been added successfully!'
    ],
    'order' => [
        'sync_selected' => 'Sync Selected',
        'sync_order' => 'Sync Order',
        'orders_sync_data' => [
            'success' => 'Orders have been synchronized successfully.',
            'failed' => 'Failed to start syncing orders.',
            'sync_purchase_order_modal_title' => 'Sync Purchase Order',
            'shop' => 'Shop',
            'select_shop' => 'Select a shop',
            'sync_total_records' => 'Sync Records Total',
            'sync_total_records_placeholder' => "Enter -1 for ALL"
        ],
        'change_status' => [
            'invalid_status' => 'Invalid status',
            'success' => 'Order status updated successfully.',
            'failed' => 'Failed ot change status of the order.'
        ],
        'delete' => [
            'success' => 'Order deleted successfully',
            'failed' => 'Failed to delete order'
        ],
        'bulk_status_update' => [
            'success' => 'Status of the orders changed successfully.',
            'failed' => 'Failed to change status.of the orders.'
        ],
        'get_pickup_address' => [
            'success' => 'Retrieved pickup address id from Lazada successfully.',
            'failed' => 'Failed to retrieved pickup address id from Lazada.'
        ],
        'get_pickup_time_slot' => [
            'success' => 'Retrieved pickup time slot from Lazada successfully.',
            'failed' => 'Failed to retrieved pickup time slot from Lazada.'
        ],
        'get_logistic_info' => [
            'success' => 'Retrieved logistic info from Lazada successfully.',
            'failed' => 'Failed to retrieved logistic info from Lazada.'
        ],
        'set_logistic_info' => [
            'success' => 'Updated logistic info from Lazada successfully.',
            'failed' => 'Failed to update logistic info from Lazada.',
            'invalid_method' => 'Shipping method is invalid.',
            'wait' => '1 hours has not passed since the order was placed.',
            'no_tracking_num' => 'Failed to retrieve tracking number.',
            'dropoff_disabled' => 'Dropoff is not allowed.'
        ],
        'datatable' => [
            'th' => [
                'order_data' => 'Order Data'
            ],
            'btn' => [
                'cancel' => 'Cancel',
                'arrange_shipment' => 'Arrange Shipment',
                'update' => 'Update',
                'delete' => 'Delete'
            ],
            'customer_name' => 'Cust. Name',
            'total_amount' => 'Total Amount',
            'total_item_s' => 'Item\s',
            'shipping_method' => 'Shipping Method',
            'order_date' => 'Order Date',
            'payment_method' => 'Payment Method'
        ],
        'product' => [
            'processing' => 'Processing...',
            'ordered_products_modal_title' => 'Ordered Products',
            'order_id' => 'Order ID',
            'details' => 'Product Details',
            'image' => 'Image',
            'id' => 'ID',
            'quantity' => 'Ordered Qty',
            'total_price' => 'Total Price'
        ],
        'customer_shipping_address' => 'Customer Shipping Address',
        'billing_to' => 'Billing To',
        'shipping_to' => 'Shipping To',
    ],
    'default_error_msg' => 'Something went wrong.',
    'no_such_order' => 'No such order found.',
    'no_such_shop' => 'No such shop found.',
    'please_select_atleast_a_row' => 'Please Select At Least 1 Row'
];
