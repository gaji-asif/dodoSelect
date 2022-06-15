<?php

return [
    'shopee' => 'Shopee',
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
        'shopee_order_statuses' => [
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
        'batch_init_selected' => 'Bulk Arrange Shipment',
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
            'failed' => 'Failed to change status of the orders.'
        ],
        'cancel_order' => [
            'success' => 'Cancelled order successfully.',
            'failed' => 'Failed to cancel the order.',
            'invalid_reason' => 'The cancellation reason is invalid.'
        ],
        'get_pickup_address' => [
            'success' => 'Retrieved pickup address id from Shopee successfully.',
            'failed' => 'Failed to retrieved pickup address id from Shopee.'
        ],
        'get_pickup_time_slot' => [
            'success' => 'Retrieved pickup time slot from Shopee successfully.',
            'failed' => 'Failed to retrieved pickup time slot from Shopee.'
        ],
        'get_branch' => [
            'success' => 'Retrieved branch id from Shopee successfully.',
            'failed' => 'Failed to retrieved branch id from Shopee.'
        ],
        'get_logistic_info' => [
            'success' => 'Retrieved logistic info from Shopee successfully.',
            'failed' => 'Failed to retrieved logistic info from Shopee.',
            'found_pickup' => 'Found pickup data.',
            'found_dropoff' => 'Found dropoff data'
        ],
        'set_logistic_info' => [
            'success' => 'Updated logistic info in Shopee successfully.',
            'failed' => 'Failed to update logistic info from Shopee.',
            'invalid_method' => 'Shipping method is invalid.',
            'missing_parameters' => 'Parameters are missing.',
            'wait' => '1 hours has not passed since the datatable was placed.',
            'no_tracking_num' => 'Failed to retrieve tracking number.',
            'dropoff_disabled' => 'Dropoff is not allowed.'
        ],
        'validate_batch_logistic_info' => [
            'success' => 'Validate logistic info in Shopee successfully.',
            'failed' => 'Failed to validate logistic info from Shopee.',
            'invalid_method' => 'Shipping method is invalid.',
            'invalid_branch' => 'Branch is invalid.',
            'missing_parameters' => 'Parameters are missing.',
            'wait' => '1 hours has not passed since the datatable was placed.',
            'empty_list' => 'The orders aren\'t valid for processing.',
            'order_limit_exceeded' => 'At most 100 orders can be send in a batch'
        ],
        'set_batch_logistic_info' => [
            'success' => 'Updated logistic info in Shopee successfully.',
            'failed' => 'Failed to update logistic info from Shopee.',
            'invalid_method' => 'Shipping method is invalid.',
            'invalid_branch' => 'Branch is invalid.',
            'missing_parameters' => 'Parameters are missing.',
            'wait' => '1 hours has not passed since the datatable was placed.',
            'empty_list' => 'The orders aren\'t valid for processing.',
            'order_limit_exceeded' => 'At most 100 orders can be send in a batch'
        ],
        'handle_order_webhook' => [
            'no_such_order' => 'No such order found.',
            'no_such_shop' => 'No such shop found.',
            'failed' => 'Failed to handle webhook data from Shopee.',
            'invalid_method' => 'Shipping method is invalid.',
            'shop_id_missing' => 'Shop id is missing',
            'sync_job_failed' => 'Failed to initiate job for retrieving single missing order send by webhook from Shopee.',
            'shop_id_not_match' => 'The shop id send by Shopee doesn\'t match with the shop_id found in database for the order.'
        ],
        'get_specific_order_airway_bill' => [
            'success' => 'Retrieved airway bill info from Shopee successfully.',
            'failed' => 'Failed to retrieved airway bill info from Shopee.',
        ],
        'datatable' => [
            'th' => [
                'order_data' => 'Order Data'
            ],
            'btn' => [
                'cancel' => 'Cancel',
                'arrange_shipment' => 'Arrange Shipment',
                'update' => 'Update',
                'delete' => 'Delete',
                'airway_bill' => 'Airway Bill'
            ],
            'customer_name' => 'Cust. Name',
            'total_amount' => 'Total Amount',
            'total_item_s' => 'Item\s',
            'shipping_method' => 'Shipping Method',
            'order_date' => 'Order Date',
            'payment_method' => 'Payment Method',
            'tracking_no' => 'Tracking No.'
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
    'please_select_atleast_a_row' => 'Please Select At Least 1 Row',
    'please_select_a_shop' => 'Please select a shop',
    'shopee_client_failed' => 'Failed to isntantiate "Shopee Client".',
    'shopee_id_not_match' => 'The shopee id did not match with the website id.'
];
