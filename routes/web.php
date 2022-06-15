<?php
use App\Http\Controllers\ChannelController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DropshipperController;
use App\Http\Controllers\ExchangeRateController;
use App\Http\Controllers\FacebookController;
use App\Http\Controllers\LineController;
use App\Http\Controllers\ProductTagController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\DefectStockController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ShopeeLogisticsController;
use App\Http\Controllers\ShopeeOrderPurchaseController;
use App\Http\Controllers\ShopeeOrderPurchaseAirwayBillController;
use App\Http\Controllers\ShopeeProductDiscountController;
use App\Http\Controllers\ShopeeWebhookController;
use App\Http\Controllers\ShopeeOrderSyncController;
use App\Http\Controllers\LazadaOrderPurchaseController;
use App\Http\Controllers\LazadaOrderPurchasePdfController;
use App\Http\Controllers\LazadaOrderSyncController;
use App\Http\Controllers\LazadaOrderStatusController;
use App\Http\Controllers\LazadaLogisiticsController;
use App\Models\Lazada;
use App\Models\LazadaSetting;
use App\Models\ShopeeSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TrackController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\ScanTranslationJobController;
use App\Http\Controllers\Admin\TranslationController;
use App\Http\Controllers\CompanyInfoSettingController;
use App\Http\Controllers\TaxRateSettingController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\BuyerPage\BankTransferConfirmController;
use App\Http\Controllers\BuyerPage\CancelOrderController;
use App\Http\Controllers\BuyerPage\PaymentMethodController;
use App\Http\Controllers\BuyerPage\SelectDistrictController;
use App\Http\Controllers\BuyerPage\SelectPostCodeController;
use App\Http\Controllers\BuyerPage\SelectProvinceController;
use App\Http\Controllers\BuyerPage\SelectSubDistrictController;
use App\Http\Controllers\BuyerPage\ShippingAddressController;
use App\Http\Controllers\BuyerPage\ShippingMethodController;
use App\Http\Controllers\Category\CategorySelectController;
use App\Http\Controllers\Category\ParentOnlySelectController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\Customer\CustomerPhoneController;
use App\Http\Controllers\CustomOrderController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SubCategoryController;
use App\Http\Controllers\OptController;
use App\Http\Controllers\OrderManage\BuyerPageController;
use App\Http\Controllers\OrderManage\PackOrderController;
use App\Http\Controllers\OrderManage\PackOrderProductController;
use App\Http\Controllers\OrderManage\ProductGridController;
use App\Http\Controllers\OrderManage\QuotationController;
use App\Http\Controllers\OrderManage\ShipmentController as OrderShipmentController;
use App\Http\Controllers\OrderManage\ShipmentLabelController;
use App\Http\Controllers\OrderManage\StatusController;
use App\Http\Controllers\OrderManage\SubCategoryGridController;

use App\Http\Controllers\WooOrderPurchase\PackWooOrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ShipperController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\OrderManageController;
use App\Http\Controllers\OrderPurchaseController;
use App\Http\Controllers\OrderPurchaseControllerNew;
use App\Http\Controllers\ProductCostController;
use App\Http\Controllers\OrderAnalysisController;

use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\Shipper\ShippingCostWeightController;
use App\Http\Controllers\ShippingCostController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ShipTypeController;
use App\Http\Controllers\DomesticShipperController;
use App\Http\Controllers\PoShipmentController;

// for woo
use App\Http\Controllers\WCProductController;
use App\Http\Controllers\WCOrderPurchaseController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\CronReportController;
use App\Http\Controllers\Dashboard\Counter\DefectStockController as CounterDefectStockController;
use App\Http\Controllers\Dashboard\Counter\LowStockController;
use App\Http\Controllers\Dashboard\Counter\OrdersTodayController;
use App\Http\Controllers\Dashboard\Counter\OrdersToProcessController;
use App\Http\Controllers\Dashboard\Counter\OutOfStockController;
use App\Http\Controllers\Dashboard\Counter\ShipmentToShipController;
use App\Http\Controllers\Dashboard\Data\HighestStockProductController;
use App\Http\Controllers\Dashboard\Data\LastChangeProductController;
use App\Http\Controllers\Dashboard\Data\LatestStockProductController;
use App\Http\Controllers\InventoryQtySyncErrorLogController;
use App\Http\Controllers\LangSwitcherController;
use App\Http\Controllers\Lazada\ExportExcelLinkedCatalogController as LazadaExportExcelLinkedCatalogController;
use App\Http\Controllers\Lazada\LinkedCatalogController as LazadaLinkedCatalogController;
use App\Http\Controllers\OrderManage\InvoiceController;
use App\Http\Controllers\WcShopController;
use App\Http\Controllers\Shop\ShopSelectController;
use App\Http\Controllers\ShopeeController;
use App\Http\Controllers\ShopeeProductsController;
use App\Http\Controllers\ShopeeProductBoostController;
use App\Http\Controllers\ShopeeOrderPurchaseHistoryController;
use App\Http\Controllers\LazadaController;
use App\Http\Controllers\LazadaProductsController;
use App\Http\Controllers\Report\StockValueController;
use App\Http\Controllers\Report\StockValueExcelController;
use App\Http\Controllers\ReservedProductLogController;
use App\Http\Controllers\SheetDataTpk\LineBotController;
use App\Http\Controllers\SheetDataTpk\OrderAnalysisController as SheetDataTpkOrderAnalysisController;
use App\Http\Controllers\SheetDataTpkBatchDeleteController;
use App\Http\Controllers\SheetDataTpkController;
use App\Http\Controllers\SheetDocController;
use App\Http\Controllers\SheetNameController;
use App\Http\Controllers\SheetNameSyncNowController;
use App\Http\Controllers\Shopee\ExportExcelLinkedCatalogController as ShopeeExportExcelLinkedCatalogController;
use App\Http\Controllers\Shopee\LinkedCatalogController as ShopeeLinkedCatalogController;
use App\Http\Controllers\ShopeeOrderController;
use App\Http\Controllers\ShopeeTransactionController;
use App\Http\Controllers\TaxInvoiceController;
use App\Http\Controllers\User\LanguageController;
use App\Http\Controllers\WCProduct\ExportExcelLinkedCatalogController;
use App\Http\Controllers\WCProduct\LinkedCatalogController;
use Lazada\LazopClient;
use Lazada\LazopRequest;
use Shopee\Client;
use Illuminate\Http\Request;
use Psr\Http\Message\ResponseInterface as ShopeeResponse;
use Psr\Http\Message\ServerRequestInterface as ShopeeRequest;
use Shopee\SignatureGenerator;
use Shopee\SignatureValidator;

Route::get('/', [TrackController::class, 'index'])->name('front page');
Route::post('/', [TrackController::class, 'getData']);
Route::get('/token', function () {
    return csrf_token();
});

Route::group(['middleware' => ['auth', 'translation']], function () {

        Route::get('/seller', function () {
            return redirect(route('dashboard'));
        });

        Route::get('/dashboard', [SellerController::class, 'dashboard'])->name('dashboard');

        Route::get('/dashboard/counter/orders-today', OrdersTodayController::class)->name('dashboard.counter.orders-today');
        Route::get('/dashboard/counter/orders-to-process', OrdersToProcessController::class)->name('dashboard.counter.orders-to-process');
        Route::get('/dashboard/counter/shipment-to-ship', ShipmentToShipController::class)->name('dashboard.counter.shipment-to-ship');
        Route::get('/dashboard/counter/low-stock', LowStockController::class)->name('dashboard.counter.low-stock');
        Route::get('/dashboard/counter/out-of-stock', OutOfStockController::class)->name('dashboard.counter.out-of-stock');
        Route::get('/dashboard/counter/defect-stock', CounterDefectStockController::class)->name('dashboard.counter.defect-stock');

        Route::get('/dashboard/data/last-change-products', LastChangeProductController::class)->name('dashboard.data.last-change-products');
        Route::get('/dashboard/data/highest-stock-products', HighestStockProductController::class)->name('dashboard.data.highest-stock-products');
        Route::get('/dashboard/data/latest-stock-products/{type}', LatestStockProductController::class)->name('dashboard.data.latest-stock-products');

        Route::get('/chart-data', [SellerController::class, 'chartData']);
        Route::get('/seller/manage-tracking', [TrackingController::class, 'index'])->name('manage tracking');
        Route::get('/tracking/data', [TrackingController::class, 'data'])->name('data tracking');
        Route::post('/tracking/insert', [TrackingController::class, 'store'])->name('insert tracking');
        Route::post('/tracking/import', [TrackingController::class, 'import'])->name('import tracking');
        Route::post('/tracking/update', [TrackingController::class, 'update'])->name('update order');
        Route::post('/tracking/delete', [TrackingController::class, 'delete'])->name('delete tracking');
        Route::get('/track-page', [TrackingController::class, 'trackPage'])->name('track page');

        //Tracking
        Route::post('/track-id', [SellerController::class, 'TrackId'])->name('Track Id');

        //Product
        Route::get('/product', [ProductController::class, 'product'])->name('product');
        Route::get('/product/data', [ProductController::class, 'data'])->name('data product');
        Route::get('/product/data_purchase_order', [ProductController::class, 'dataPurchaseOrder'])->name('data purchase order');
        Route::get('/product/data_order_details', [ProductController::class, 'dataOrderDetails'])->name('data order details');

        Route::get('/product/data_order_details_ps', [ProductController::class, 'dataOrderDetailsPs'])->name('data order details ps');

        Route::get('/product/data_order_details_rnp', [ProductController::class, 'dataOrderDetailsReservedNotPaid'])->name('data order details reservedNotPaid');

        Route::post('/product/insert', [ProductController::class, 'insert'])->name('insert product');
        Route::get('/product/edit-tag', [ProductController::class, 'editProductTag'])->name('product.edit_tag');
        Route::post('/product/update-tag', [ProductController::class, 'updateProductTag'])->name('product.update_tag');
        Route::post('/product/update', [ProductController::class, 'update'])->name('product.update');
        Route::post('/product/delete', [ProductController::class, 'delete'])->name('product.delete');
        Route::post('/product/bulk-import', [ProductController::class, 'bulkImport'])->name('product_bulk_import');
        Route::get('/product/select', [ProductController::class, 'selectTwoHandler'])->name('product.select2');
        Route::get('/product/select/seller', [ProductController::class, 'selectTwoBySellerHandler'])->name('product.select2.seller');
        Route::get('/product/bulk-auto-link', [ProductController::class, 'bulkAutoLink'])->name('product_bulk_auto_link');
        Route::get('/product/bulk-sync', [ProductController::class, 'bulkAutoSync'])->name('product_bulk_sync');

        Route::post('/po/delete', [OrderPurchaseController::class, 'orderPurchaseDelete'])->name('delete po');
        Route::post('/order/delete', [OrderManageController::class, 'orderDelete'])->name('delete order');
        Route::post('/delete-bulk-product', [ProductController::class, 'deleteBulkProduct'])->name('delete_bulk_product');

        //quantity update
        Route::get('seller/product/data', [ProductController::class, 'productData'])->name('product data seller');
        Route::post('seller/product/update', [ProductController::class, 'productUpdate'])->name('seller quantity update');
        Route::get('seller/see-details/datatable', [ProductController::class, 'seeDetailsDataTable'])->name('seller-quantity-details-datatable');
        Route::get('seller/see-reseved-quantity-details/datatable', [ReservedProductLogController::class, 'seeDetailsDataTable'])->name('seller-reserved-quantity-details-datatable');
        Route::get('seller/see-details/{id}', [ProductController::class, 'seeDetails'])->name('seller quantity details');
        Route::get('seller/see-reserved-quantity-log-details/{id}', [ReservedProductLogController::class, 'seeReservedQuantityLogDetails'])->name('seller_reserved_quantity_log_details');

        Route::get('/date-quantity-log', [ProductController::class, 'dataQuantityLog'])->name('date quantity log');
        Route::post('/update-quantity-log', [ProductController::class, 'updateQuantityLog'])->name('update quantity log');
        Route::post('/delete-quantity-log', [ProductController::class, 'deleteQuantityLog'])->name('delete quantity log');
        Route::post('/delete-quantity-log-bulk', [ProductController::class, 'deleteQuantityLogBulk'])->name('delete quantity log bulk');

        // woo quantity update
        Route::post('/product/inventory-sync/shops', [ProductController::class, 'getShopsList'])->name('product.inventory_sync.shop_list');
        Route::post('/product/inventory-sync/filter-quantities', [ProductController::class, 'getFilterQuantities'])->name('product.inventory_sync.filter_quantities');
        Route::get('/product/inventory-sync/datatable', [ProductController::class, 'inventorySyncDataTable'])->name('product.inventory_sync.datatable');
        Route::get('/product/inventory-sync/{id}', [ProductController::class, 'inventorySync'])->name('product.inventory_sync');
        Route::post('product/save-link', [ProductController::class, 'saveProductLink'])->name('product.save_link');
        Route::post('product/save-multiple-links', [ProductController::class, 'saveMultipleProductLinks'])->name('product.save_multiple_links');
        Route::post('/product/inventory-auto-link', [ProductController::class, 'inventoryAutoLink'])->name('product.inventory_auto_link');
        Route::post('/product/inventory-sync-quantity', [ProductController::class, 'inventorySyncQuantity'])->name('product.inventory_sync.quantity');

        Route::get('/product/{id}', [ProductController::class, 'show'])->name('product.show');
        Route::get('/product/child/sku/search', [ProductController::class, 'searchChildSku'])->name('product.child.sku.search');
        Route::post('/product/child/sku/add/{product_code}', [ProductController::class, 'addChildSku'])->name('product.child.sku.add');
        Route::post('/product/child/sku/delete/{product_code}', [ProductController::class, 'deleteChildSku'])->name('product.child.sku.delete');

        //staff
        Route::get('manage-staff', [StaffController::class, 'manageStaff'])->name('staff.manage');
        Route::get('staff/data', [StaffController::class, 'data'])->name('staff.data');
        Route::post('staff/insert', [StaffController::class, 'insert'])->name('staff.insert');
        Route::post('staff/update', [StaffController::class, 'update'])->name('staff.update');
        Route::post('staff/delete', [StaffController::class, 'delete'])->name('staff.delete');
        Route::get('staff/password-modal', [StaffController::class, 'changePasswordModal'])->name('staff.change_password_modal');
        Route::post('staff/change-password', [StaffController::class, 'changePassword'])->name('staff.change_password');

        // Roles and permissions
        Route::get('roles', [RoleController::class, 'staffRole'])->name('role');
        Route::get('roles/data', [RoleController::class, 'data'])->name('role.data');
        Route::post('roles/insert', [RoleController::class, 'insert'])->name('role.insert');
        Route::post('roles/update', [RoleController::class, 'update'])->name('role.update');
        Route::post('roles/delete', [RoleController::class, 'delete'])->name('role.delete');

        Route::get('roles/assign/{id}', [RoleController::class, 'assignPermission'])->name('role.assign_permission');
        Route::post('roles/save-assign/{id}', [RoleController::class, 'saveAssignPermission'])->name('assign_permission.save');
        Route::get('roles/no-role', [RoleController::class, 'noRole'])->name('no_role_profile');

        Route::get('permissions', [RoleController::class, 'permissions'])->name('staff.permissions');
        Route::get('permissions/data', [RoleController::class, 'dataPermission'])->name('staff.permissions.data');
        Route::post('permissions/insert', [RoleController::class, 'insertPermission'])->name('staff.permissions.insert');
        Route::post('permissions/update', [RoleController::class, 'updatePermission'])->name('staff.permissions.update');
        Route::post('permissions/delete', [RoleController::class, 'deletePermission'])->name('staff.permissions.delete');

        // Route::get('/purchase_order', [PurchaseOrderController::class, 'index'])->name('purchase order');
        Route::get('order_management/status', [StatusController::class, 'index'])->name('order_manage.status.index');
        Route::post('order_management/order_status', [StatusController::class, 'getOrderStatusList'])->name('order_manage.status.list');
        Route::get('order_management/product-grid', [ProductGridController::class, 'index'])->name('order_manage.product-grid.index');
        Route::get('order_management/sub-category-grid', [SubCategoryGridController::class, 'index'])->name('order_manage.sub-category-grid.index');
        Route::get('order_management/pack-order/products', [PackOrderProductController::class, 'index'])->name('order_manage.pack-order.product.index');

        Route::get('order_managements/create/{customerType?}', [OrderManageController::class, 'create'])->name('order_management.create');
        Route::get('order_management/{customerType?}', [OrderManageController::class, 'index'])->name('order_management.index');
        Route::post('order_management/update', [ OrderManageController::class, 'update' ])->name('order_management.update');
        Route::post('order_management/cancel', [ OrderManageController::class, 'orderManagementCancel' ])->name('cancel order');

        Route::get('order_management/{order_id}-{shipment_id}/shipment-label', [ShipmentLabelController::class, 'printShipmentPdf'])->name('shipment-label.pdf');

        Route::resource('order_management', OrderManageController::class)->except([ 'index', 'show', 'update', 'create' ]);
        Route::get('order_management/quotation/pdf/{order_id}', [QuotationController::class, 'printPdf'])->name('order_manage.quotation.pdf');
        Route::get('order_management/invoice/pdf/{order_id}', [InvoiceController::class, 'printPdf'])->name('order_manage.invoice.pdf');

        //Route::resource('all_shipment', ShipmentController::class);
        Route::get('all_shipment/{shipment_for}', [ShipmentController::class, 'index'])->name('all_shipment_index');

        Route::post('shipment', [OrderShipmentController::class, 'store'])->name('shipment.store');
        Route::post('shipmentForOrder', [OrderShipmentController::class, 'storeForOrder'])->name('shipment.storeForOrder');
        Route::post('shipmentUpdateForOrder', [OrderShipmentController::class, 'shipmentUpdateForOrder'])->name('shipment.shipmentUpdateForOrder');
        Route::post('shipment/update', [OrderShipmentController::class, 'update'])->name('shipment.update');
        Route::post('shipment/delete', [OrderShipmentController::class, 'destroy'])->name('shipment.destroy');
        Route::post('shipment/pack-order', [PackOrderController::class, 'update'])->name('shipment.pack-order');
        Route::post('shipment/pack-woo-order', [PackWooOrderController::class, 'update'])->name('shipment.pack-woo-order');

        //WOO Shipments
        Route::get('shipment/woo-shipments', [ShipmentController::class, 'wooShipments'])->name('shipment.woo-shipments');
        Route::get('all_woo_shipment_list/list', [ShipmentController::class, 'dataWooShipments'])->name('all_woo_shipment_list');
        Route::get('getWOOCustomerOrderHistory', [ShipmentController::class, 'getWOOCustomerOrderHistory']);

        // Custom Order
        Route::get('custom-order/datatable', [ CustomOrderController::class, 'dataTable' ])->name('custom-order.datatable');
        Route::post('custom-order/update', [CustomOrderController::class, 'update'])->name('custom-order.update');
        Route::post('custom-order/delete', [CustomOrderController::class, 'destroy'])->name('custom-order.destroy');
        Route::resource('custom-order', CustomOrderController::class)->except([ 'show', 'update', 'destroy' ]);

        // Route::post('/storeOrderData', [OrderManageController::class, 'store'])->name('storeOrderData');

        Route::get('order_manage/list', [OrderManageController::class, 'data'])->name('ordersList');
        Route::get('all_shipment_list/list', [ShipmentController::class, 'data'])->name('all_shipment_list');
        Route::get('all_shipment_list_order/list', [OrderManageController::class, 'dataAllShipmentsOrders'])->name('all_shipment_list_order');
        Route::get('all_shipment_list_for_order/list', [OrderManageController::class, 'allShipmentData'])->name('all_shipment_list_for_order');
        Route::post('createShipment', [OrderManageController::class, 'createShipment'])->name('createShipment');

        // Route::get('get-Sub-Catgeory', [OrderManageController::class,'getAllSubCatgeory']);
        Route::get('get-Sub-Catgeory/list', [OrderManageController::class, 'getAllSubCatgeory'])->name('getAllSubCatgeory');
        Route::get('get-all-pro-Sub-Catgeory', [OrderManageController::class,'getAllProductCatWise'])->name('getAllProductCatWise');


        Route::get('getSubCatName', [OrderManageController::class,'getSubCatName'])->name('getSubCatName');

        Route::post('/order_manage/bulkStatus', [OrderManageController::class, 'bulkStatus'])->name('data bulkStatus');


        Route::post('bulkShipment', [OrderManageController::class, 'bulkShipment'])->name('bulkShipment');

        Route::post('order_management_update/{id}', [OrderManageController::class,'orderManagementUpdate']);
        Route::get('order_management-delete/{id}', [OrderManageController::class,'orderManagementDelete']);

        Route::get('check_customer_phone', [OrderManageController::class,'check_customer_phone']);
    Route::get('order_management-show/get_ordered_products', [OrderManageController::class, 'getOrderedProducts'])->name('get_ordered_dodo_products');
    Route::get('order_management-show/get_shipping_address', [OrderManageController::class, 'getShippingAddress'])->name('get_shipping_address');

    //seetings

        //categories
        Route::resource('categories', CategoryController::class)->except([ 'create', 'show', 'edit', 'update', 'destroy' ]);
        Route::get('categories/select', [CategorySelectController::class, 'index'])->name('categories.select');
        Route::get('categories-parent/select', [ParentOnlySelectController::class, 'index'])->name('categories-parent.select');
        Route::get('product_list/select', [ParentOnlySelectController::class, 'getProducts'])->name('product_list.select');


        Route::POST('categories_update/{id}',[ CategoryController::class,'updateCategory']);
        Route::get('/categories-delete/{id}', [CategoryController::class, 'delete']);
        Route::POST('post-sortable',[ CategoryController::class,'reOrder']);
        Route::POST('/categories/update_sub_categories',[ CategoryController::class,'updateSubCategory'])->name('update sub category');
        //sub categories
        Route::get('sub_categories', [SubCategoryController::class, 'index'])->name('sub_categories');
        // Route::resource('/sub_categories', SubCategoryController::class);
        Route::get('/sub_categories/data', [SubCategoryController::class, 'data'])->name('data sub category');
        Route::post('/sub_categories/store', [SubCategoryController::class, 'store'])->name('store sub category');
        // Route::POST('/sub_categories/update',[ SubCategoryController::class,'updateCategory'])->name('update sub category');
        Route::post('/sub_categories/delete', [SubCategoryController::class, 'delete'])->name('delete sub category');

        Route::get('categories/get-all-sub-categories', [CategoryController::class, 'getAllSubCategories'])->name('get all sub categories');
        Route::post('categories/fetch-sub-categories', [CategoryController::class, 'fetchSubCategory'])->name('fetch sub categories');


        //supplier
        Route::get('suppliers', [SupplierController::class, 'index'])->name('suppliers');
        Route::get('/suppliers/data', [SupplierController::class, 'data'])->name('data suppliers');
        Route::post('/suppliers/store', [SupplierController::class, 'store'])->name('store supplier');
        Route::post('/suppliers/update',[ SupplierController::class,'update'])->name('update supplier');
        Route::post('/suppliers/delete', [SupplierController::class, 'delete'])->name('delete supplier');

        //shop
        Route::get('shops', [ShopController::class, 'index'])->name('shops');
        Route::get('/shops/data', [ShopController::class, 'data'])->name('shops.data');
        Route::get('/shops/create', [ShopController::class, 'create'])->name('shop.create');
        Route::post('/shops/store', [ShopController::class, 'store'])->name('shop.store');
        Route::post('/shops/update',[ ShopController::class,'update'])->name('shop.update');
        Route::post('/shops/delete', [ShopController::class, 'delete'])->name('shop.delete');

        Route::get('shops/select', [ ShopSelectController::class, 'index' ])->name('shop.select');

        //channels
        Route::resource('channels', ChannelController::class);
        Route::post('channels-update/{id}',[ChannelController::class,'update']);
        Route::get('channels-delete/{id}', [ChannelController::class, 'delete']);

        //exchange-rates
        Route::resource('exchange-rates', ExchangeRateController::class);
        Route::post('exchange-rates-update/{id}',[ExchangeRateController::class,'update']);
        Route::get('exchange-rates-delete/{id}', [ExchangeRateController::class, 'delete']);


        //shiptypes
        Route::resource('ship-types', ShipTypeController::class);
        Route::post('ship-types-update/{id}',[ShipTypeController::class,'update']);
        Route::get('ship-types-delete/{id}', [ShipTypeController::class, 'delete']);


        //product tags
        Route::resource('product-tags', ProductTagController::class);
        Route::post('product-tags-update/{id}',[ProductTagController::class,'update']);
        Route::get('product-tags-delete/{id}', [ProductTagController::class, 'delete']);


        //inventory qty sync error log
        Route::group(['prefix' => 'inventory-qty-sync-error-log'], function () {
            Route::get('', [InventoryQtySyncErrorLogController::class, 'index'])->name('inventory_qty_sync_error_log_index');
            Route::get('data', [InventoryQtySyncErrorLogController::class, 'getErrorLogDetailsDataTable'])->name('inventory_qty_sync_error_log_index.datatable');
            Route::post('delete-inventory-qty-error-log', [InventoryQtySyncErrorLogController::class, 'delete'])->name('inventory_qty_sync_error_log_index.delete');
            Route::post('delete-all-inventory-qty-error-log', [InventoryQtySyncErrorLogController::class, 'deleteAll'])->name('inventory_qty_sync_error_log_index.delete_all');
        });

        //order purchase
        Route::resource('order_purchase', OrderPurchaseController::class);
        Route::get('order_purchase_list', [OrderPurchaseController::class, 'data'])->name('order_purchase_list');
        Route::get('/po/pdf/{id}', [OrderPurchaseController::class, 'pdfview']);

        Route::post('change_otder_purchase_status', [OrderPurchaseController::class,'changeOrderPurchaseStatus']);
        Route::get('/get-shipping-mark-order-purchase', [OrderPurchaseController::class, 'getShippingMarkByShippingTypeId'])->name('get_shipping_mark_by_shipping_type_id');

        Route::get('/get_product_wise_po_shipping_info', [OrderPurchaseController::class, 'getProductWisePOShippingInfo'])->name('get_product_wise_po_shipping_info');
        Route::post('/add_po_shipment', [OrderPurchaseController::class, 'addPoShipment'])->name('add_po_shipment');
        Route::get('/get_po_shipment_details_by_order_purchase_id', [OrderPurchaseController::class, 'getTotalShippedQtyByPOIDAndProductID'])->name('get_po_shipment_details_by_order_purchase_id');
        Route::post('/edit_po_shipment', [OrderPurchaseController::class, 'poShipmentEdit'])->name('edit_po_shipment');
        Route::post('/delete_po_shipment', [OrderPurchaseController::class, 'poShipmentDelete'])->name('delete_po_shipment');

        // PO Shipment Datatable and actions From PO Edit page  >> Page URL : .... order_purchase/{id}/edit
        Route::get('shipment_data_table_on_po_edit_page', [OrderPurchaseController::class, 'ShowShipmentTableOnPOeditPage'])->name('shipment_data_table_on_po_edit_page');


        // PO Shipment Datatable and actions From PO Shimpent page >> Page URL : ../po_shipments
        Route::group(['prefix' => 'po_shipments'], function () {
            Route::get('/', [PoShipmentController::class, 'shipments'])->name('po_shipments');
            Route::get('/single_edit_form', [PoShipmentController::class, 'SinglePOEditForm'])->name('single edit po_shipment form');
            Route::get('/data', [PoShipmentController::class, 'listData'])->name('data_po_shipments');
            Route::get('/edit_form', [PoShipmentController::class, 'LoadPOShipmentEditForm'])->name('edit shipment form');
            Route::post('/update_po_shipment', [PoShipmentController::class, 'updatePoShipment'])->name('update po_shipment');
            Route::post('/delete', [PoShipmentController::class, 'PoShipmentDelete'])->name('delete po_shipment');
        });


        //product cost price
        Route::group(['prefix' => 'product_cost'], function () {
            Route::get('/', [ProductCostController::class, 'productCost'])->name('product_cost');
            Route::get('/data', [ProductCostController::class, 'listData'])->name('data_product_cost');
            Route::get('/create', [ProductCostController::class, 'createProductCost'])->name('create product cost');
            Route::post('/store', [ProductCostController::class, 'storeProductCost'])->name('store product cost');
            Route::get('/product-cost-markup-profit-calculation', [ProductCostController::class, 'markUpLowestSellPriceAndProfitCalculation'])->name('product_cost_markup_profit_calculation');
            Route::get('/update_form', [ProductCostController::class, 'updateForm'])->name('update cost form');
            Route::post('/update_product_cost', [ProductCostController::class, 'updateProductCost'])->name('update product lowest cost');
            Route::get('/show_reoder_form', [ProductCostController::class, 'showReOrderForm'])->name('show product reorder form');
            Route::post('/update_reoder_data', [ProductCostController::class, 'updateReOrderData'])->name('update product reorder data');
        });

        Route::group(['prefix' => 'order_analysis'], function () {
            Route::get('/', [OrderAnalysisController::class, 'orderAnalysis'])->name('order_analysis');
            Route::get('/datatable', [OrderAnalysisController::class, 'loadOrderAnalysisDataTable'])->name('datatable_order_analysis');
            Route::get('/update_form', [PurchaseOrderController::class, 'updateForm'])->name('update form');
            Route::post('/update_reorder_stock', [PurchaseOrderController::class, 'updateReorderStock'])->name('update reorder stock');

        });


        // Cost Analysis Page >>URL : .../cost_analysis
        Route::group(['prefix' => 'cost_analysis'], function () {
            Route::get('/', [ProductCostController::class, 'productCostAnalysis'])->name('cost_analysis');
            Route::get('/data', [ProductCostController::class, 'datatableCostAnalysis'])->name('datatable product cost analysis');
            Route::post('/update', [ProductCostController::class, 'changeProductCostPrice'])->name('update product cost');
        });

        Route::get('po_product_analysis', [PurchaseOrderController::class, 'POProductAnalysisData'])->name('data_product_analysis');
        Route::get('export order analysis', [PurchaseOrderController::class, 'ExportOrderAnalysis'])->name('export order analysis');

        Route::get('po_settings', [PurchaseOrderController::class, 'poSettings'])->name('po_settings');
        Route::post('china_cargo_store', [PurchaseOrderController::class, 'storeChinaCargo'])->name('store china cargo');
        Route::get('china_cargo_store/create', [PurchaseOrderController::class, 'createChinaCargo'])->name('create china cargo');
        Route::get('china-cargo-edit/{id}', [PurchaseOrderController::class, 'editFormChinaCargo'])->name('edit form china cargo');
        Route::post('china_cargo_update', [PurchaseOrderController::class, 'updateChinaCargo'])->name('update china cargo');
        Route::get('china-cargo-delete/{id}', [PurchaseOrderController::class, 'deleteChinaCargo']);
        Route::post('domestic_shipper', [PurchaseOrderController::class, 'domesticShipper'])->name('domestic_shipper');
        Route::get('/get_qr_code', [QrCodeController::class, 'get_qr_code']);


         //domestic_shippers
         Route::resource('domestic_shippers', DomesticShipperController::class);
         Route::post('domestic_shippers-update/{id}',[DomesticShipperController::class,'update']);
         Route::get('domestic_shippers-delete/{id}', [DomesticShipperController::class, 'delete']);


        //manage shipping
        Route::get('manage-shipper', [ShipperController::class, 'index'])->name('manage shipper');
        Route::get('shipper/data', [ShipperController::class, 'data'])->name('data shipper');
        Route::post('shipper/insert', [ShipperController::class, 'insert'])->name('insert shipper');
        Route::post('shipper/update', [ShipperController::class, 'update'])->name('update shipper');
        Route::post('shipper/delete', [ShipperController::class, 'delete'])->name('delete shipper');

        Route::get('shipper/shipping-cost', [ ShippingCostWeightController::class, 'show' ])->name('shipper.shipping-cost.weight');

        //shipping cost
        Route::get('add-cost/{id}', [ShippingCostController::class, 'index'])->name('add-shipping-cost');
        Route::resource('shipping-cost', ShippingCostController::class)->except([ 'index', 'create', 'show', 'update', 'destroy' ]);
        Route::POST('shipping-cost_update/{id}',[ ShippingCostController::class,'updateShippingCost']);
        Route::get('/shipping-cost-delete/{id}', [ShippingCostController::class, 'delete']);
        Route::get('/shipping-cost-edit/{id}/{shipper_id}', [ShippingCostController::class, 'shippingCostEdit']);

        //report
        Route::get('/report-stock', [ReportController::class, 'report'])->name('product_report');
        Route::get('/report/data', [ReportController::class, 'reportData'])->name('data_product_report');
        // Route::post('filter_product_report', [ReportController::class, 'filterReport'])->name('filter_report');

        Route::get('report/stock-value', [StockValueController::class, 'index'])->name('report.stock-value.index');
        Route::get('report/stock-value/datatable', [StockValueController::class, 'datatable'])->name('report.stock-value.datatable');
        Route::get('report/stock-value/summary', [StockValueController::class, 'summary'])->name('report.stock-value.summary');
        Route::get('report/stock-value/show/{id}', [StockValueController::class, 'show'])->name('report.stock-value.show');

        Route::get('report/stock-value/export-excel', StockValueExcelController::class)->name('report.stock-value.export-excel');

        Route::get('/report-stock-movements', [ReportController::class, 'stockReport'])->name('stock_movement_report');
        Route::get('/report-stock-movements/data', [ReportController::class, 'stockReportData'])->name('data_stock_movement_report');

        Route::get('activity-log', [ReportController::class, 'activityLog'])->name('activity_log');
        Route::get('/activity-log/data', [ReportController::class, 'dataActivityLog'])->name('activity_log.data');
        Route::post('/undo-activity-log', [ReportController::class, 'undoActivityLog'])->name('activity_log.undo');

        Route::get('shopee-transaction', [ShopeeTransactionController::class, 'index'])->name('shopee-transaction.index');
        Route::get('shopee-transaction/datatable', [ShopeeTransactionController::class, 'datatable'])->name('shopee-transaction.datatable');
        Route::post('shopee-transaction/store', [ShopeeTransactionController::class, 'store'])->name('shopee-transaction.store');
        Route::get('shopee-transaction/show/{id}', [ShopeeTransactionController::class, 'show'])->name('shopee-transaction.show');
        Route::get('shopee-transaction/summary', [ShopeeTransactionController::class, 'summary'])->name('shopee-transaction.summary');
        Route::get('shopee-transaction/sync-status', [ShopeeTransactionController::class, 'syncStatus'])->name('shopee-transaction.sync-status');

        Route::get('shopee-order', [ShopeeOrderController::class, 'index'])->name('shopee-order.index');
        Route::get('shopee-order/datatable', [ShopeeOrderController::class, 'datatable'])->name('shopee-order.datatable');
        Route::get('shopee-order/show/{id}', [ShopeeOrderController::class, 'show'])->name('shopee-order.show');
        Route::get('shopee-order/summary', [ShopeeOrderController::class, 'summary'])->name('shopee-order.summary');

        // customers
        Route::get('customer', [CustomerController::class, 'index'])->name('customer');
        Route::get('customer/data', [CustomerController::class, 'data'])->name('customer.data');
        Route::post('customer/store', [CustomerController::class, 'store'])->name('customer.store');
        Route::post('customer/update', [CustomerController::class, 'update'])->name('customer.update');
        Route::post('customer/delete', [CustomerController::class, 'delete'])->name('customer.delete');
        Route::get('customer/orders/datatable', [CustomerController::class, 'orderListData'])->name('customer.order_datatable');
        Route::get('customer/orders/{id}', [CustomerController::class, 'orderList'])->name('customer.order_list');
        Route::get('customer/custom-orders/datatable', [CustomerController::class, 'customOrderListData'])->name('customer.custom_order_datatable');
        Route::get('customer/custom-orders/{id}', [CustomerController::class, 'customOrderList'])->name('customer.custom_order_list');

        // Drop shippers
        Route::get('manage-dropshippers', [DropshipperController::class, 'index'])->name('dropshippers');
        Route::get('dropshippers/data', [DropshipperController::class, 'data'])->name('dropshipper.data');
        Route::get('dropshippers/create', [DropshipperController::class, 'create'])->name('dropshipper.create');
        Route::post('dropshippers/store', [DropshipperController::class, 'store'])->name('dropshipper.store');
        Route::post('dropshippers/update', [DropshipperController::class, 'update'])->name('dropshipper.update');
        Route::post('dropshippers/delete', [DropshipperController::class, 'delete'])->name('dropshipper.delete');

        // Dropshippers Roles and Permissions
        Route::get('dropshipper/roles', [DropshipperController::class, 'dropshipperRole'])->name('dropshipper.role');
        Route::get('dropshipper/roles/data', [DropshipperController::class, 'dropshipperData'])->name('dropshipper.role_data');

        Route::get('dropshipper/roles/permissions/datatable', [DropshipperController::class, 'dataDropshipperPermissionByRole'])->name('dropshipper.assign_permission.role_datatable');
        Route::get('dropshipper/roles/permissions/{id}', [DropshipperController::class, 'dropshipperPermissionByRole'])->name('dropshipper.assign_permission.role');
        Route::post('dropshipper/roles/save-assign', [DropshipperController::class, 'dropshipperAssignPermissionByRole'])->name('dropshipper.assign_permission.role_save');

        Route::get('dropshippers/permissions/datatable', [DropshipperController::class, 'dataDropshipperPermissionByUser'])->name('dropshipper.assign_permission.user_datatable');
        Route::get('dropshippers/permissions/{id}', [DropshipperController::class, 'dropshipperPermissionByUser'])->name('dropshipper.assign_permission.user');
        Route::post('dropshippers/save-assign', [DropshipperController::class, 'dropshipperAssignPermissionByUser'])->name('dropshipper.assign_permission.user_save');

        Route::get('dropshipper/orders', [DropshipperController::class, 'dropshipperOrders'])->name('dropshipper.orders');
        Route::get('dropshipper/orders/data', [DropshipperController::class, 'dropshipperOrdersData'])->name('dropshipper.orders_datatable');


        Route::get('customer-phone', [ CustomerPhoneController::class, 'show' ])->name('customer-phone.show');

        Route::get('tax-rate-settings', [TaxRateSettingController::class, 'index'])->name('tax-rate-settings.index');
        Route::post('tax-rate-settings/update', [TaxRateSettingController::class, 'update'])->name('tax-rate-settings.update');
        Route::get('company-info-settings', [CompanyInfoSettingController::class, 'index'])->name('company-info-settings.index');
        Route::post('company-info-settings/update', [CompanyInfoSettingController::class, 'update'])->name('company-info-settings.update');

        Route::get('tax-invoice', [TaxInvoiceController::class, 'index'])->name('tax-invoice.index');
        Route::get('tax-invoice/datatable', [TaxInvoiceController::class, 'dataTable'])->name('tax-invoice.datatable');
        Route::get('tax-invoice/pdf/{order_id}', [TaxInvoiceController::class, 'generatePdfInvoice'])->name('tax-invoice.pdf-invoice');
//});

    Route::group(['prefix' => 'admin', 'middleware' => 'admin'], function () {
        Route::get('dashboard', [AdminController::class, 'dashboard'])->name('admin dashboard');
        Route::get('manage-seller', [AdminController::class, 'manageSeller'])->name('manage seller');
        Route::get('seller/data', [AdminController::class, 'data'])->name('data seller');
        Route::post('seller/insert', [AdminController::class, 'insert'])->name('insert seller');
        Route::post('seller/update', [AdminController::class, 'update'])->name('update seller');
        Route::post('seller/delete', [AdminController::class, 'delete'])->name('delete seller');

        Route::get('/user-logo', [AdminController::class, 'userLogo'])->name('user logo');
        Route::post('/user-logo-update', [AdminController::class, 'uploadUserLogo'])->name('upload user logo');
         //package
         Route::get('/package', [SellerController::class, 'package'])->name('package');
         Route::get('/package/data', [SellerController::class, 'data'])->name('data package');
         Route::post('/package/insert', [SellerController::class, 'insert'])->name('insert package');
         Route::post('/package/update', [SellerController::class, 'update'])->name('update package');
         Route::post('/package/delete', [SellerController::class, 'delete'])->name('delete package');
         //trackinglog
         Route::get('seller/tracking-log/{id}', [AdminController::class, 'trackingLog'])->name('seller tracking log');

        Route::get('translation', [TranslationController::class, 'index'])->name('translation.index');
        Route::post('translation/store', [TranslationController::class, 'store'])->name('translation.store');
        Route::post('translation/update', [TranslationController::class, 'update'])->name('translation.update');
        Route::post('translation/delete', [TranslationController::class, 'delete'])->name('translation.delete');
        Route::get('translation/datatable', [ TranslationController::class, 'dataTable' ])->name('translation.datatable');
        Route::get('translation/show/{id}', [TranslationController::class, 'show'])->name('translation.show');

        Route::get('scan-translation-job', [ScanTranslationJobController::class, 'index'])->name('scan-translation-job.index');
    });

    //gerereate qr code
    Route::get('/generate-qr-code', [QrCodeController::class, 'generateQrCode'])->name('generate qr code');

    Route::get('/view-qr-code/{id}', [QrCodeController::class, 'viewQrCode'])->name('view qr code');
    Route::post('/add-product-code', [QrCodeController::class, 'addProductCode'])->name('add product_code');
    Route::post('/generate_qr_code_pdf', [QrCodeController::class, 'generateQrCodePdf1'])->name('print qr code');

    Route::get('/in-out', [QrCodeController::class, 'inOutWithQrCode'])->name('inout qr code');
    Route::get('/in-out/history', [QrCodeController::class, 'inOutHistory'])->name('in-out-history');
    Route::get('/in-out/history/{id}', [QrCodeController::class, 'inOutHistoryDetail'])->name('in-out-history-detail');
    Route::get('/in-out/datatable', [QrCodeController::class, 'inOutDataTable'])->name('in-out-datatable');
    Route::post('/in-out/history/update', [QrCodeController::class, 'inOutHistoryUpdate'])->name('in-out-history-update');
    Route::post('/in-out/history/delete', [QrCodeController::class, 'inOutHistoryDelete'])->name('in-out-history-delete');

    Route::get('/get-qr-code-product', [QrCodeController::class, 'getQrCodeProduct'])->name('get_qr_code_product');
    Route::get('/get-qr-code-productget-order-purchase', [QrCodeController::class, 'getQrCodeProductForOrderPurchase'])->name('get_qr_code_product_order_purchase');

    Route::get('/get-qr-code-productget-order_managment', [QrCodeController::class, 'getQrCodeProductForOrderManagment'])->name('get_qr_code_product_order_managment');
    Route::get('/reset-qr-code-product', [QrCodeController::class, 'resetQrCodeProduct'])->name('reset_session_product');
    Route::get('/delete-session-product', [QrCodeController::class, 'deleteSessionProduct'])->name('delete_session_product');
    Route::get('/delete-session-product2', [QrCodeController::class, 'deleteSessionProduct2'])->name('delete_session_product2');
    Route::post('/submit-input', [QrCodeController::class, 'updateInOut'])->name('submit input');
    Route::post('/autocomplete/getAutocomplete/',[QrCodeController::class, 'getAutocomplete'])->name('Autocomplte.getAutocomplte');

    // Defect stocks
    Route::get('/defect-stock', [DefectStockController::class, 'index'])->name('defect-stock');
    Route::get('/defect-stock/data', [DefectStockController::class, 'data'])->name('defect_stock.data');
    Route::get('/defect-stock/show', [DefectStockController::class, 'show'])->name('defect_stock.show');
    Route::get('/defect-stock/show-result', [DefectStockController::class, 'showResult'])->name('defect_stock.show_result');
    Route::get('/defect-stock/create', [DefectStockController::class, 'create'])->name('defect_stock.create');
    Route::get('/get-qr-code-for-defect-stocks', [DefectStockController::class, 'getQrCodeProductForDefectStock'])->name('get-qr-code-for-defect-stocks');
    Route::post('/defect-stock/autocomplete',[DefectStockController::class, 'defectStockAutocomplete'])->name('defect_stock.autocomplete');
    Route::post('/defect-stock/store', [DefectStockController::class, 'store'])->name('defect_stock.store');
    Route::post('/defect-stock/update', [DefectStockController::class, 'update'])->name('defect_stock.update');
    Route::post('/defect-stock/delete', [DefectStockController::class, 'defectStockDelete'])->name('defect_stock.delete');
    Route::get('/reset-qr-code-defect-product', [DefectStockController::class, 'resetQrCodeDefectProduct'])->name('reset_session_defect_product');
    Route::get('/delete-session-defect-product', [DefectStockController::class, 'deleteSessionDefectProduct'])->name('delete_session_defect_product');
    Route::post('/defect-stock/update-gallery', [DefectStockController::class, 'addGallery'])->name('update gallery');

    Route::post('address_autocomplete',[OrderManageController::class, 'address_autocomplete'])->name('address_autocomplete');

    Route::get('/your_packages', [AccountController::class, 'yourPackages'])->name('your_packages');
    Route::get('/profile', [AccountController::class, 'profile'])->name('profile');
    Route::post('/profile-update', [AccountController::class, 'profileUpdate']);
    Route::post('/change-password', [AccountController::class, 'changePassword'])->name('change_password');

    Route::post('change-language', [LanguageController::class, 'update'])->name('user.change-language');

    Route::get('/wc_products/data', [WCProductController::class, 'data'])->name('wc_products');
    Route::post('/wc_products/sync-product', [WCProductController::class, 'syncProduct'])->name('wc_products_sync');
    Route::post('/wc_products/getPaginationData', [WCProductController::class, 'getPaginationData'])->name('wc_products pagination');
    Route::get('/wc_products/wc_data_product', [WCProductController::class, 'wc_data_product'])->name('wc_data_product');
    Route::post('/wc_products/showInventoryLink', [WCProductController::class, 'showInventoryLink'])->name('data show_inventory_link');
    Route::get('/wc_products/getVariationByID', [WCProductController::class, 'getVariationByID'])->name('data get_variations_by_id');
    Route::get('delete_session_add_linked_product', [WCProductController::class, 'delete_session_add_linked_product'])->name('delete_session_add_linked_product');
    Route::post('/wc_products/sync-products', [WCProductController::class, 'bulkSync'])->name('bulkSync');

    Route::get('/wc-products/export-excel/linked-catalog', ExportExcelLinkedCatalogController::class)->name('wc-products.export-excel-linked-catalog');

    Route::post('/wc-product/linked-catalog', [LinkedCatalogController::class, 'store'])->name('wc-product.linked-catalog.store');
    Route::get('/wc-product/linked-catalog/datatable', [LinkedCatalogController::class, 'dataTable'])->name('wc-product.linked-catalog.datatable');

     Route::get('/wc_products/{website_id}/{product_id}', [WCProductController::class, 'edit'])->name('wc-products-edit');
     Route::get('/wc-products-create', [WCProductController::class, 'create'])->name('wc-products-create');
     Route::post('deleteWooProductImage', [WCProductController::class, 'deleteWooProductImage'])->name('woo.product.delete_product_images');
     Route::post('uploadWooProductImage', [WCProductController::class, 'uploadWooProductImage'])->name('woo.product.upload_product_images');
     Route::get('/wc-products-show/{product_id}', [WCProductController::class, 'show'])->name('wc-products-show');

    Route::post('wc_products_store', [WCProductController::class, 'store'])->name('wc_products.store');

    Route::post('wc_products/update', [WCProductController::class, 'update'])->name('wc_products.update');
    Route::post('wc_products/delete', [WCProductController::class, 'delete'])->name('wc_products.delete');
    Route::resource('wc_products', WCProductController::class)->only(['index', 'show']);
});


Route::get('verify_mobile', [OptController::class, 'verifyMobile'])->name('verify_mobile');
Route::post('/get-otp', [OptController::class, 'getOtp'])->name('get-otp');
Route::post('/reset-pass', [OptController::class, 'resetpass'])->name('reset-pass');
Route::post('/get-phone', [OptController::class, 'getPhone'])->name('get-phone');
Route::get('/forget_password', [OptController::class, 'forgetPassword'])->name('forget-password');
Route::get('/reset_password', [OptController::class, 'resetPassword'])->name('reset-password');

//order_mangment_buyer_pages
Route::get('order_status/{order_id}', [BuyerPageController::class, 'orderStatus'])->name('order-status');
Route::get('payment-order-notify/{order_id}', [BuyerPageController::class, 'paymentOrderNotify'])->name('payment-order-notify');
// Route::get('order_management_buyer/{order_id}', [OrderManageController::class, 'buyerPage'])->name('order-management.public-url');
Route::get('order_management_buyer/dropshipper', [OrderManageController::class, 'createDropshipperOrder'])->name('order-management.public-url-dropshipper');
Route::post('order_management_buyer/dropshipper-order', [OrderManageController::class, 'storeDropshipperOrder'])->name('order-management.dropshipper.place-order');

Route::get('orders/{order_id}', [BuyerPageController::class, 'edit'])->name('order-management.public-url')->middleware(['translation.public']);
Route::post('orders/{order_id}', [BuyerPageController::class, 'update'])->name('order-management.buyer.place-order')->middleware(['translation.public']);

Route::post('changePaymentMethod', [BuyerPageController::class, 'changePaymentMethod']);

Route::post('buyer-page/shipping-address/check-address', [ShippingAddressController::class, 'checkShippingAddress'])->name('buyer-page.shipping-address.check-address');
Route::post('buyer-page/shipping-method/update/{order_id}', [ShippingMethodController::class, 'update'])->name('buyer-page.shipping-method.update');
Route::post('buyer-page/shipping-address/update/{order_id}', [ShippingAddressController::class, 'update'])->name('buyer-page.shipping-address.update');
Route::post('buyer-page/bank-transfer-confirm/{order_id}', [BankTransferConfirmController::class, 'store'])->name('buyer-page.bank-transfer-confirm.store');
Route::post('buyer-page/cancel-order/{order_id}', [CancelOrderController::class, 'store'])->name('buyer-page.cancel-order');

Route::get('buyer-page/select-province', SelectProvinceController::class)->name('buyer-page.select-province');
Route::get('buyer-page/select-district', SelectDistrictController::class)->name('buyer-page.select-district');
Route::get('buyer-page/select-sub-district', SelectSubDistrictController::class)->name('buyer-page.select-sub-district');
Route::get('buyer-page/select-post-code', SelectPostCodeController::class)->name('buyer-page.select-post-code');

Route::get('payment_success_notify', [OrderManageController::class, 'paymentSuccessNotify']);

Route::get('get_all_shipping_methods', [OrderManageController::class, 'get_all_shipping_methods']);
Route::get('getOrderHistory', [OrderManageController::class, 'getOrderHistory']);

//Line WebHook
Route::post('line/webhook', [LineController::class, 'triggeredPayload'])->name('line.webhook.payload');
Route::post('line/webhook/ac-sale-notify', [LineController::class, 'triggeredPayloadACSale'])->name('line.webhook.payload.ac');
Route::post('line/webhook/tpk-sale-notify', [LineBotController::class, 'webhook'])->name('line.webhook.payload.tpk');

Route::post('/make_order_payment', [OrderManageController::class, 'makeOrderPayment'])->name('make_order_payment');

Route::get('isPaymentSuccess', [OrderManageController::class, 'isPaymentSuccess']);
require __DIR__ . '/auth.php';

// All Route area start For Shipments
Route::get('getCustomerOrderHistory', [ShipmentController::class, 'getCustomerOrderHistory']);

Route::get('getOrderPaymentDetails', [OrderManageController::class, 'getOrderPaymentDetails']);


Route::post('/printLevelPrint', [ShipmentController::class, 'printLevelPrint'])->name('printLevelPrint');
Route::post('/printLevelBulk', [ShipmentController::class, 'printLevelBulk'])->name('printLevelBulk');
Route::post('/orderPrintLabelBulk', [ShipmentController::class, 'orderPrintLabelBulk'])->name('orderPrintLabelBulk');

Route::get('getCustomerOrderHistoryForPack', [ShipmentController::class, 'getCustomerOrderHistoryForPack']);

Route::post('/updateShipmentStatus', [ShipmentController::class, 'updateShipmentStatus'])->name('updateShipmentStatus');
Route::post('shipment/update-ship-status', [ShipmentController::class, 'updateShipmentStatusByUser'])->name('shipment.update-ship-status');
Route::post('order/update-order-status', [OrderManageController::class, 'updateOrderStatusByUser'])->name('order.update-order-status');


Route::get('getAllOrderedPro', [OrderManageController::class, 'getAllOrderedPro']);
Route::get('getAllOrderedProForOrder', [OrderManageController::class, 'getAllOrderedProForOrder']);
Route::get('getAllOrderedProForOrderEdit', [OrderManageController::class, 'getAllOrderedProForOrderEdit']);

Route::post('/deleteShipment', [ShipmentController::class, 'deleteShipment'])->name('deleteShipment');
Route::get('/deleteShipmentForOrder', [OrderManageController::class, 'deleteShipmentForOrder'])->name('deleteShipmentForOrder');
Route::get('/deleteCustomShipmentForOrder', [OrderManageController::class, 'deleteCustomShipmentForOrder'])->name('deleteCustomShipmentForOrder');
Route::get('getCustomerOrderHistoryForDelete', [ShipmentController::class, 'getCustomerOrderHistoryForDelete']);

Route::post('/confirmPaymentForOrder', [OrderManageController::class, 'confirmPaymentForOrder'])->name('confirmPaymentForOrder');

Route::post('/makeNewPayment', [OrderManageController::class, 'makeNewPayment'])->name('makeNewPayment');

Route::post('/order_take_action', [OrderManageController::class, 'orderTakeAction'])->name('order_take_action');

Route::get('getManualPaymentData', [OrderManageController::class, 'getManualPaymentData']);

Route::post('/updateManualPayment', [OrderManageController::class, 'updateManualPayment'])->name('updateManualPayment');

Route::get('delManualPaymentData', [OrderManageController::class, 'delManualPaymentData']);
Route::get('changeBankPaymentStatus', [OrderManageController::class, 'changeBankPaymentStatus']);
Route::get('getShipmentDetailsData', [OrderManageController::class, 'getShipmentDetailsData']);

Route::get('getCustomShipmentDetailsData', [OrderManageController::class, 'getCustomShipmentDetailsData']);
Route::get('markAsShipped', [OrderManageController::class, 'markAsShipped']);
Route::get('getModalContentForCustomShipment', [OrderManageController::class, 'getModalContentForCustomShipment']);

Route::get('getModalContentForEditCustomShipment', [OrderManageController::class, 'getModalContentForEditCustomShipment']);

Route::get('getOrderedProductDetails', [OrderManageController::class, 'getOrderedProductDetails']);

Route::post('storeForCustomShipment', [OrderShipmentController::class, 'storeForCustomShipment'])->name('shipment.storeForCustomShipment');

Route::post('updateForCustomShipment', [OrderShipmentController::class, 'updateForCustomShipment'])->name('shipment.updateForCustomShipment');


 //WC Controller
 //Order purchase
 Route::get('/wc-order-purchase/getCustomerAddress', [WCOrderPurchaseController::class, 'getCustomerAddress'])->name('data customer address');
 Route::get('/wc-order-purchase/getOrderProducts', [WCOrderPurchaseController::class, 'getOrderProducts'])->name('data order products');
 Route::get('/wc-order-purchase/getShipmentProducts', [WCOrderPurchaseController::class, 'getShipmentProducts'])->name('data shipment products');
 Route::get('/wc-order-purchase/getOrderStatus', [WCOrderPurchaseController::class, 'getOrderStatus'])->name('data order status');

 Route::post('/wc-order-purchase/bulkSync', [WCOrderPurchaseController::class, 'bulkSync'])->name('data bulkSync');
Route::post('/wc-order-purchase/bulkStatus', [WCOrderPurchaseController::class, 'bulkStatus'])->name('wc data bulkStatus');
Route::post('changeOrderPurchaseStatus', [WCOrderPurchaseController::class,'changeOrderPurchaseStatus'])->name('wc_change_order_purchase_status');
 Route::get('/wc-order-purchase/data', [WCOrderPurchaseController::class, 'data'])->name('data order');
 Route::get('/wc-order-purchase/sync', [WCOrderPurchaseController::class, 'sync'])->name('sync');
 Route::post('/wc-order-purchase/getCountryStateSyncData', [WCOrderPurchaseController::class, 'getCountryStateSyncData'])->name('wc_country_state_sync');
 Route::resource('wc-order-purchase', WCOrderPurchaseController::class);
 Route::post('/wc-order-purchase/getOrderSyncData', [WCOrderPurchaseController::class, 'getOrderSyncData'])->name('wc_orders_sync_manually');

 Route::post('/wc-order-purchase/wc_order_delete', [WCOrderPurchaseController::class, 'wc_order_delete'])->name('wc_order_delete');

Route::post('wc-order-purchase/woo_status', [StatusController::class, 'getWooStatusList'])->name('wc_order.status.get_woo_status');
Route::post('wc-order-purchase/woo_shipment_status', [StatusController::class, 'getWooShipmentStatusList'])->name('wc_order.status.get_woo_shipment_status');
Route::post('wc-order-purchase/woo_status_list', [StatusController::class, 'getWooOrderStatusList'])->name('wc_order.status.woo_status_list');


Route::post('order_purchase_update/{id}', [WCOrderPurchaseController::class,'orderPurchaseUpdate']);
 Route::get('order_purchase-delete/{id}', [WCOrderPurchaseController::class,'orderPurchaseDelete']);
 Route::get('wc-order-purchase-details/{id}/{shop_id}', [WCOrderPurchaseController::class,'orderPurchaseDetails'])->name('wc-order-purchase-details');
 Route::post('wc_change_order_address', [WCOrderPurchaseController::class,'changeOrderPurchaseAddress']);
 Route::get('getWCShipmentDetailsData', [WCOrderPurchaseController::class, 'getWCShipmentDetailsData']);
 Route::get('arrangeShipment', [WCOrderPurchaseController::class, 'arrangeShipment']);
 Route::get('getAllWCOrderedProForOrder', [WCOrderPurchaseController::class, 'getAllWCOrderedProForOrder']);
 Route::get('getAllWCOrderedProductForOrShipment', [WCOrderPurchaseController::class, 'getAllWCOrderedProductForOrShipment']);

 Route::get('getAllWCOrderedProForOrderEdit', [WCOrderPurchaseController::class, 'getAllWCOrderedProForOrderEdit']);
 Route::get('/deleteWCShipmentForOrder', [WCOrderPurchaseController::class, 'deleteWCShipmentForOrder'])->name('deleteWCShipmentForOrder');
 Route::post('WCStorArrangeShipmentForOrder', [WCOrderPurchaseController::class, 'WCStorArrangeShipmentForOrder'])->name('WCshipment.arrangeForOrder');
 Route::post('WCshipmentForOrder', [WCOrderPurchaseController::class, 'WCshipmentForOrder'])->name('WCshipment.storeForOrder');
 Route::post('WCshipmentUpdateForOrder', [WCOrderPurchaseController::class, 'WCshipmentUpdateForOrder'])->name('shipment.WCshipmentUpdateForOrder');
 Route::post('/updateWCShipmentStatus', [WCOrderPurchaseController::class, 'updateWCShipmentStatus'])->name('updateWCShipmentStatus');
 Route::get('getWCCustomShipmentDetailsData', [WCOrderPurchaseController::class, 'getWCCustomShipmentDetailsData']);
 Route::get('getModalContentForWCCustomShipment', [WCOrderPurchaseController::class, 'getModalContentForWCCustomShipment']);
 Route::get('getWOOProductDetails', [WCOrderPurchaseController::class, 'getWOOProductDetails']);
 Route::get('WCproduct_list/select', [WCOrderPurchaseController::class, 'getProducts'])->name('WCproduct_list.select');
 Route::post('storeForWCCustomShipment', [WCOrderPurchaseController::class, 'storeForWCCustomShipment'])->name('shipment.storeForWCCustomShipment');
 Route::get('getModalContentForEditWCCustomShipment', [WCOrderPurchaseController::class, 'getModalContentForEditWCCustomShipment']);
 Route::post('updateForWCCustomShipment', [WCOrderPurchaseController::class, 'updateForWCCustomShipment'])->name('shipment.updateForWCCustomShipment');
 Route::get('WCmarkAsShipped', [WCOrderPurchaseController::class, 'WCmarkAsShipped']);
 Route::post('wc-order-purchase/update-status', [WCOrderPurchaseController::class, 'updateWCOrderStatus'])->name('wc_order.update-order-status');

 Route::get('wc-order-purchase/quotation/pdf/{order_id}/{shop_id}', [WCOrderPurchaseController::class, 'printWCQuotationPdf'])->name('wc-order-purchase.quotation.pdf');
 Route::get('wc-order-purchase/invoice/pdf/{order_id}/{shop_id}', [WCOrderPurchaseController::class, 'printWCinvoicePdf'])->name('wc-order-purchase.invoice.pdf');
 Route::get('/cron-report', [CronReportController::class,"index"])->name('cronReport');

 //WC Stocks
 Route::get('/wc_stocks/getVariationByID', [InventoryController::class, 'getVariationByID'])->name('data get_inventories_variations_by_id');
 Route::post('/wc_stocks/addToInventory', [InventoryController::class, 'addToInventory'])->name('data add_to_inventory');
  Route::post('/wc_stocks/addProductBySKU', [InventoryController::class, 'addProductBySKU'])->name('data add_product_by_sku');
 Route::any('/wc_stocks/searchProductBySKU', [InventoryController::class, 'searchProductBySKU'])->name('autocomplete.fetch');
 Route::any('/wc_stocks/autocomplete_inventrory', [InventoryController::class, 'autocompleteInventrory'])->name('autocomplete.inventrory');
 Route::get('/wc_stocks/getLinkedProductBySKU', [InventoryController::class, 'getLinkedProductBySKU'])->name('data inventory products');
 Route::get('/wc_stocks/editProductForm', [InventoryController::class, 'editStockForm'])->name('data stock_edit_form');
 Route::get('/wc_stocks/data', [InventoryController::class, 'data'])->name('data stock');
 Route::resource('wc_stocks', InventoryController::class);
 Route::get('create-inventory', [InventoryController::class,"createInventory"])->name('createInventory');
 Route::get('edit-inventory', [InventoryController::class,"editInventory"])->name('editInventory');
 Route::get('inventory-to-product', [InventoryController::class,"inventoryToProduct"])->name('inventoryToProduct');
 Route::get('inventories', [InventoryController::class,"inventories"])->name('inventories');
 Route::get('inventories_product', [InventoryController::class,"inventoryProduct"])->name('inventoryProduct');
 Route::post('inventories_delete', [InventoryController::class,"inventoriesDelete"])->name('inventoriesDelete');
 Route::post('inventories_product_delete', [InventoryController::class,"inventoryProductDelete"])->name('inventoryProductDelete');
 Route::post('inventories_product_add', [InventoryController::class,"inventoryProductAdd"])->name('inventoryProductAdd');
 Route::get('SyncProduct', [InventoryController::class,"SyncProduct"])->name('SyncProduct');
 Route::get('SyncAllInv', [InventoryController::class,"SyncAllInv"])->name('SyncAllInv');

// All Route area start For Shipments
Route::get('getWCCustomerOrderHistory', [WCOrderPurchaseController::class, 'getWCCustomerOrderHistory']);
Route::get('getWCCustomerOrderHistoryForPack', [WCOrderPurchaseController::class, 'getWCCustomerOrderHistoryForPack']);
Route::get('getWCCustomerOrderHistoryForCustomShipment', [WCOrderPurchaseController::class, 'getWCCustomerOrderHistoryForCustomShipment']);
Route::get('getWCCustomerOrderHistoryForPackAndCustomShipment', [WCOrderPurchaseController::class, 'getWCCustomerOrderHistoryForPackAndCustomShipment']);
Route::post('/WCprintLabelPrint', [WCOrderPurchaseController::class, 'WCprintLabelPrint'])->name('WCprintLabelPrint');
Route::post('/WCprintLevelBulk', [WCOrderPurchaseController::class, 'WCprintLevelBulk'])->name('WCprintLevelBulk');

///all sync order data
Route::any('/order-sync-all', [WCOrderPurchaseController::class, 'orderSyncAll'])->name('wc_orders_sync');
Route::any('/product-sync-all', [WCProductController::class, 'productSyncAll'])->name('wc_orders_sync');

//manage shops
Route::get('woo-settings', [WcShopController::class,"wooSettings"])->name('woo-settings');
Route::resource('wc-shops', WcShopController::class);
Route::POST('shops_update/{id}',[ WcShopController::class,'updateShop']);
Route::get('/shops-delete/{id}', [WcShopController::class, 'delete']);
Route::get('/shops-refresh/{id}', [WcShopController::class, 'shopRefresh']);

Route::get('test', [ShipmentController::class,"testCheck"])->name('testCheck');

// Shopee
Route::group(['prefix' => 'shopee'], function () {
    Route::get('/', [ShopeeController::class, 'index'])->name('shopee.index');
    Route::get('settings', [ShopeeController::class, 'settings'])->name('shopee.settings');
    Route::get('authorization', [ShopeeController::class, 'authorization'])->name('shopee.authorization');
    Route::post('add/shop', [ShopeeController::class, 'add'])->name('shopee.add.shop');
    Route::post('update/shop/{id}', [ShopeeController::class, 'update'])->name('shopee.update.shop');
    Route::post('delete/shop/{id}', [ShopeeController::class, 'delete'])->name('shopee.delete.shop');

    Route::group(['prefix' => 'product'], function () {
        Route::get('/', [ShopeeController::class, 'product'])->name('shopee.product.index');
        Route::post('sync', [ShopeeController::class, 'sync'])->name('shopee.product.sync');
        Route::get('data', [ShopeeController::class, 'data'])->name('shopee.products');
        Route::get('edit', [ShopeeController::class, 'edit'])->name('shopee.product.edit');
        Route::post('update', [ShopeeController::class, 'product_update'])->name('shopee.product.update');
        Route::post('delete', [ShopeeController::class, 'product_delete'])->name('shopee.product.delete');
        Route::get('show/{id}', [ShopeeController::class, 'show'])->name('shopee.product.show');
        Route::post('getLogisticsFromShopee', [ShopeeLogisticsController::class, 'getLogisticsFromShopee'])->name('shopee.product.get_logistics');

        Route::get('createShopeeProduct', [ShopeeProductsController::class, 'create'])->name('shopee.product.create_page');
        Route::post('storeShopeeProduct', [ShopeeProductsController::class, 'store'])->name('shopee.product.store_product');
        Route::post('getProductAttributesFromShopee', [ShopeeProductsController::class, 'getProductAttributesFromShopee'])->name('shopee.product.get_attributes');
        Route::get('editShopeeProduct/{id}', [ShopeeProductsController::class, 'edit'])->name('shopee.product.edit_page');
        Route::post('updateShopeeProduct', [ShopeeProductsController::class, 'update'])->name('shopee.product.update_product');
        Route::post('deleteShopeeProduct', [ShopeeProductsController::class, 'delete'])->name('shopee.product.delete_product');

        Route::post('updateShopeeProductImage', [ShopeeProductsController::class, 'updateShopeeProductImage'])->name('shopee.product.update_product_images');
        Route::post('deleteShopeeProductImage', [ShopeeProductsController::class, 'deleteShopeeProductImage'])->name('shopee.product.delete_product_images');
        Route::post('getShopeeProductCategory', [ShopeeProductsController::class, 'getShopeeProductCategory'])->name('shopee.product.get_category');
        Route::post('updateShopeeVariationProductImage', [ShopeeProductsController::class, 'updateShopeeVariationSpecificProductImage'])->name('shopee.product.update_variation_product_image');
        Route::post('getShopeeProductSubCategory', [ShopeeProductsController::class, 'getShopeeProductSubCategory'])->name('shopee.product.get_sub_category');
        Route::post('getShopeeProductSubSubCategory', [ShopeeProductsController::class, 'getShopeeProductSubSubCategory'])->name('shopee.product.get_sub_sub_category');
        Route::post('getShopeeProductMissingInfo', [ShopeeProductsController::class, 'getShopeeProductMissingInfo'])->name('shopee.product.get_missing_info');

        Route::get('export-excel/linked-catalog', ShopeeExportExcelLinkedCatalogController::class)->name('shopee.export-excel-linked-catalog');

        Route::group(['prefix' => 'boost'], function () {
            Route::get('/', [ShopeeProductBoostController::class, 'product'])->name('shopee.product.boost.index');
            Route::get('data', [ShopeeProductBoostController::class, 'data'])->name('shopee.product.boost.data');
            Route::post('getBoostedProductsFromShopee', [ShopeeProductBoostController::class, 'getBoostedProductsFromShopee'])->name('shopee.product.boost.get_boosted_products');
            Route::post('getTotalBoostedProductsForSpecificShopeeShop', [ShopeeProductBoostController::class, 'getTotalBoostedProductsForSpecificShopeeShop'])->name('shopee.product.boost.get_boosted_products_count');
            Route::post('setBoostedProductsFromShopee', [ShopeeProductBoostController::class, 'setBoostedProductsFromShopee'])->name('shopee.product.boost.set_boosted_products');
            Route::post('updateBoostRepeatForQueuedBoostedProducts', [ShopeeProductBoostController::class, 'updateBoostRepeatForQueuedBoostedProducts'])->name('shopee.product.update_boost_repeat_for_queued_products');
            Route::post('removeShopeeProductForBoostingFromQueue', [ShopeeProductBoostController::class, 'removeShopeeProductForBoostingFromQueue'])->name('shopee.product.remove_product_from_queue_for_boosting');
        });

        Route::group(['prefix' => 'discount'], function(){
           Route::get('/', [ShopeeProductDiscountController::class, 'index'])->name('shopee.product.discount.index');
           Route::get('/datatable/data', [ShopeeProductDiscountController::class, 'data'])->name('shopee.product.discount.data');
           Route::post('/sync', [ShopeeProductDiscountController::class, 'sync'])->name('shopee.product.discount.sync');
           Route::post('/manage-renew', [ShopeeProductDiscountController::class, 'manageRenewableDiscounts'])->name('shopee.product.discount.manage_renew');
        });
    });

    Route::group(['prefix' => 'order'], function () {
        Route::get('/', [ShopeeOrderPurchaseController::class, 'index'])->name('shopee.order.index');
        Route::get('data', [ShopeeOrderPurchaseController::class, 'data'])->name('shopee.order.data');
        Route::get('dataAllShopeeShipments', [ShopeeOrderPurchaseController::class, 'dataAllShopeeShipments'])->name('shopee.order.dataAllShopeeShipments');
        Route::get('getOrderStatus', [ShopeeOrderPurchaseController::class, 'getOrderStatus'])->name('shopee.order.order_status');
        Route::post('changeOrderPurchaseStatus', [ShopeeOrderPurchaseController::class,'changeOrderPurchaseStatus'])->name('shopee.order.change_status');
        Route::get('getOrderProducts', [ShopeeOrderPurchaseController::class, 'getOrderProducts'])->name('shopee.order.products');
        Route::post('delete', [ShopeeOrderPurchaseController::class, 'deleteSpecificOrder'])->name('shopee.order.delete');
        Route::post('bulkStatusUpdate', [ShopeeOrderPurchaseController::class, 'bulkStatusUpdateForSelectedOrders'])->name('shopee.order.bulk_status_update');
        Route::get('getCustomerAddress', [ShopeeOrderPurchaseController::class, 'getCustomerAddress'])->name('shopee.display_customer_address');
        Route::post('getPickupAddressFromShopee', [ShopeeOrderPurchaseController::class, 'getPickupAddressIdsFromShopee'])->name('shopee.order.get_pickup_address');
        Route::post('getPickupTimeSlotFromShopee', [ShopeeOrderPurchaseController::class, 'getTimeSlotFromShopee'])->name('shopee.order.get_pickup_time_slot');
        Route::post('getBranchInfoFromShopee', [ShopeeOrderPurchaseController::class, 'getBranchInfoFromShopee'])->name('shopee.order.get_branch_info');
        Route::post('getBranchInfoFromShopee1', [ShopeeOrderPurchaseController::class, 'getBranchInfoFromDatabseForShopee'])->name('shopee.order.get_branch_info_1');
        Route::post('getBranchStatesInfoForShopee', [ShopeeOrderPurchaseController::class, 'getBranchStatesInfoForShopee'])->name('shopee.order.get_branch_states_info');
        Route::post('getBranchCitiesInfoForShopee', [ShopeeOrderPurchaseController::class, 'getBranchCitiesInfoForShopee'])->name('shopee.order.get_branch_cities_info');
        Route::post('getLogisticInfoFromShopee', [ShopeeOrderPurchaseController::class, 'getLogisticInfoFromShopee'])->name('shopee.order.get_logistic_info');
        Route::post('setLogisticInfoInShopee', [ShopeeOrderPurchaseController::class, 'setLogisticInfoInShopee'])->name('shopee.order.set_logistic_info');
        Route::post('validateOrdersLogisticInfoInBatchInShopee', [ShopeeOrderPurchaseController::class, 'validateOrdersLogisticInfoInBatchInShopee'])->name('shopee.order.validate_orders_batch_logistic_info');
        Route::post('setLogisticInfoInBatchInShopee', [ShopeeOrderPurchaseController::class, 'setLogisticInfoInBatchInShopee'])->name('shopee.order.set_batch_logistic_info');
        Route::post('cancelSpecificOrderInShopee', [ShopeeOrderPurchaseController::class, 'cancelSpecificOrderInShopee'])->name('shopee.order.cancel_specific_order');
        Route::post('getShopeeStatusList', [ShopeeOrderPurchaseController::class, 'getShopeeStatusList'])->name('shopee.order.get_status_custom_list');
        Route::post('markShopeeOrderAsShippedToWarehouse', [ShopeeOrderPurchaseController::class, 'markShopeeOrderAsShippedToWarehouse'])->name('shopee.order.mark_order_as_shipped_to_warehouse');
        Route::post('markShopeeOrderAsPickupConfirmed', [ShopeeOrderPurchaseController::class, 'markShopeeOrderAsPickupConfirmed'])->name('shopee.order.mark_order_as_confirm_pickup');
        Route::get('getMissingAwburlForShopeeOrdersCountInSession', [ShopeeOrderPurchaseController::class, 'getMissingAwburlForShopeeOrdersCountInSession'])->name('shopee.order.get_missing_awb_url_and_tracking_no_count');
        Route::post('getShopeeShippingMethodsWithOrderCount', [ShopeeOrderPurchaseController::class, 'getShopeeShippingMethodsWithOrderCount'])->name('shopee.order.get_shopee_shipement_methods_with_count');
        Route::post('getShopeeOrdersProcessingNowForInit', [ShopeeOrderPurchaseController::class, 'getShopeeOrdersProcessingNowForInit'])->name('shopee.order.get_shopee_orders_processing_now_for_init');
        Route::post('getShopeeOrdersHavingMissingTrackingNumUpdated', [ShopeeOrderPurchaseController::class, 'getShopeeOrdersHavingMissingTrackingNumUpdated'])->name('shopee.order.get_shopee_orders_having_missing_tracking_number_updated');
        Route::post('setLogisticInfoInBatchForMultipleShopsInShopee', [ShopeeOrderPurchaseController::class, 'setLogisticInfoInBatchForMultipleShopsInShopee'])->name('shopee.order.set_batch_logistic_info_mltp_shop');
        Route::post('checkIfProcessingBatchInit', [ShopeeOrderPurchaseController::class, 'checkIfShopeeOrderBulkInitIsStillProcessingOrders'])->name('shopee.order.check_processing_batch_init');

        Route::post('webhook', [ShopeeWebhookController::class, 'handleShopeeOrderWebhook'])->name('shopee.order.handle_order_webhook');

        Route::post('bulkShopeeAirwayBillPrint', [ShopeeOrderPurchaseAirwayBillController::class, 'bulkShopeeAirwayBillPrint'])->name('shopee.order.print_airway_bill_in_bulk');
        Route::post('generateShopeeAirwayBillPrint', [ShopeeOrderPurchaseAirwayBillController::class, 'generateShopeeAirwayBillPrint'])->name('shopee.order.generate_airway_bill_in_bulk');
        Route::get('getDownloadableShopeeAirwayBillPrint', [ShopeeOrderPurchaseAirwayBillController::class, 'getDownloadableShopeeAirwayBillPrint'])->name('shopee.order.get_downloadable_airway_bill');
        Route::get('downloadShopeeAirwayBillPrint/{token}', [ShopeeOrderPurchaseAirwayBillController::class, 'downloadShopeeAirwayBillPrint'])->name('shopee.order.download_airway_bill');
        Route::post('deleteSpecificShopeeAirwayBill', [ShopeeOrderPurchaseAirwayBillController::class, 'deleteSpecificShopeeAirwayBill'])->name('shopee.order.delete_airway_bill');
        Route::post('deleteOldShopeeAirwayBill', [ShopeeOrderPurchaseAirwayBillController::class, 'deleteOldShopeeAirwayBill'])->name('shopee.order.delete_all_airway_bill');
        Route::post('checkIfPdfCanBeGenerated', [ShopeeOrderPurchaseAirwayBillController::class, 'checkIfPdfCanBeGenerated'])->name('shopee.order.can_generate_airway_bill');
        Route::post('getSpecificOrderAirwayBillInfoFromShopee', [ShopeeOrderPurchaseAirwayBillController::class, 'getSpecificOrderAirwayBillInfoFromShopee'])->name('shopee.order.get_specific_order_airway_bill');

        Route::post('getOrderSyncData', [ShopeeOrderSyncController::class, 'getOrderSyncData'])->name('shopee.order.orders_sync_data');
        Route::post('getShopeeShopsForBulkSyncingModal', [ShopeeOrderSyncController::class, 'getShopeeShopsForBulkSyncingModal'])->name('shopee.order.shops_for_bulk_order_syncing');
        Route::post('bulkSyncSelectedOrders', [ShopeeOrderSyncController::class, 'bulkSyncSelectedOrders'])->name('shopee.order.bulk_sync_selected_order');

        Route::get('syncOldOrdersFromShopee', [ShopeeOrderSyncController::class, 'syncOldOrdersFromShopee'])->name('shopee.order.sync_old_orders');

        Route::get('order-history-analysis', [ShopeeOrderPurchaseHistoryController::class, 'index'])->name('shopee.order.order_history_analysis');
        Route::post('order-history-analysis/data', [ShopeeOrderPurchaseHistoryController::class, 'data'])->name('shopee.order.order_history_analysis.data');

        Route::get('history', [ShopeeOrderPurchaseHistoryController::class, 'index'])->name('shopee.order.history');
        Route::post('history/data', [ShopeeOrderPurchaseHistoryController::class, 'data'])->name('shopee.order.history.data');
    });

    Route::group([
        'prefix' => 'linked-catalog'
    ], function () {
        Route::get('datatable', [ShopeeLinkedCatalogController::class, 'dataTable'])->name('shopee.linked-catalog.datatable');
        Route::post('store', [ShopeeLinkedCatalogController::class, 'store'])->name('shopee.linked-catalog.store');
    });
});

// Lazada
Route::group(['prefix' => 'lazada'], function () {
    Route::get('/', [LazadaController::class, 'index'])->name('lazada.index');
    Route::get('settings', [LazadaController::class, 'settings'])->name('lazada.settings');
    Route::get('authorization', [LazadaController::class, 'authorization'])->name('lazada.authorization');
    Route::post('refresh/token/{id}', [LazadaController::class, 'refresh_token'])->name('lazada.refresh.token');
    Route::post('add/shop', [LazadaController::class, 'add'])->name('lazada.add.shop');
    Route::post('update/shop/{id}', [LazadaController::class, 'update'])->name('lazada.update.shop');
    Route::post('delete/shop/{id}', [LazadaController::class, 'delete'])->name('lazada.delete.shop');

    Route::group(['prefix' => 'product'], function () {
        Route::get('/', [LazadaController::class, 'product'])->name('lazada.product.index');
        Route::get('sync', [LazadaController::class, 'sync'])->name('lazada.product.sync');
        Route::get('data', [LazadaController::class, 'data'])->name('lazada.products');
        Route::get('edit', [LazadaController::class, 'edit'])->name('lazada.product.edit');
        Route::post('update', [LazadaController::class, 'product_update'])->name('lazada.product.update');
        // Route::post('delete', [LazadaController::class, 'product_delete'])->name('lazada.product.delete');
        Route::get('show/{id}', [LazadaController::class, 'show'])->name('lazada.product.show');
        // Route::get('edit-page/{id}', [LazadaController::class, 'editPage'])->name('lazada.product.edit_page');
        // Route::post('updateProduct', [LazadaController::class, 'productUpdate'])->name('lazada.product.update_product');
        Route::post('updateLazadaProductImage', [LazadaeController::class, 'updateLazadaProductImage'])->name('lazada.product.update_product_images');

        Route::get('createLazadaProduct', [LazadaProductsController::class, 'create'])->name('lazada.product.create_page');
        Route::post('storeLazadaProduct', [LazadaProductsController::class, 'store'])->name('lazada.product.store_product');
        Route::get('editLazadaProduct/{id}', [LazadaProductsController::class, 'edit'])->name('lazada.product.edit_page');
        Route::post('updateLazadaProduct', [LazadaProductsController::class, 'update'])->name('lazada.product.update_product');
        Route::post('getLazadaProductCategory', [LazadaProductsController::class, 'getLazadaProductCategory'])->name('lazada.product.get_category');
        Route::post('getLazadaProductSubCategory', [LazadaProductsController::class, 'getLazadaProductSubCategory'])->name('lazada.product.get_sub_category');
        Route::post('getLazadaProductSubSubCategory', [LazadaProductsController::class, 'getLazadaProductSubSubCategory'])->name('lazada.product.get_sub_sub_category');
        Route::post('getProductAttributesFromLazada', [LazadaProductsController::class, 'getProductCategoryWiseAttributesFromLazada'])->name('lazada.product.get_category_wise_attributes');
        Route::post('getProductBrandsFromLazada', [LazadaProductsController::class, 'getProductBrandsFromLazada'])->name('lazada.product.get_brands');
        Route::post('deleteLazadaProduct', [LazadaProductsController::class, 'delete'])->name('lazada.product.delete');
        Route::post('deleteLazadaProductVariationImage', [LazadaProductsController::class, 'deleteLazadaProductVariationImage'])->name('lazada.product.delete_product_variation_image');

        Route::get('export-excel/linked-catalog', LazadaExportExcelLinkedCatalogController::class)->name('lazada.export-excel-linked-catalog');
    });

    Route::group(['prefix' => 'order'], function () {
        Route::get('/',  [LazadaOrderPurchaseController::class, 'index'])->name('lazada.order.index');
        Route::get('data', [LazadaOrderPurchaseController::class, 'data'])->name('lazada.order.data');
        Route::get('getCustomerAddress', [LazadaOrderPurchaseController::class, 'getCustomerAddress'])->name('lazada.display_customer_address');
        Route::get('getOrderProducts', [LazadaOrderPurchaseController::class, 'getOrderProducts'])->name('lazada.order.products');

        Route::post('getLazadaStatusList', [LazadaOrderStatusController::class, 'getLazadaStatusList'])->name('lazada.order.get_status_custom_list');
        Route::post('setStatusToPackedByMarketplace', [LazadaOrderStatusController::class, 'setStatusToPackedByMarketplace'])->name('lazada.status.set_status_to_packed_to_marketplace');
        Route::post('setStatusToPackedByMarketplaceInBulk', [LazadaOrderStatusController::class, 'setStatusToPackedByMarketplaceInBulk'])->name('lazada.status.set_status_to_packed_to_marketplace_in_bulk');
        Route::post('setStatusToReadyToShip', [LazadaOrderStatusController::class, 'setStatusToReadyToShip'])->name('lazada.status.set_status_to_ready_to_ship');
        Route::post('setStatusToReadyToShipInBulk', [LazadaOrderStatusController::class, 'setStatusToReadyToShipInBulk'])->name('lazada.status.set_status_to_ready_to_ship_in_bulk');
        Route::post('markLazadaOrderAsShippedToWarehouse', [LazadaOrderStatusController::class, 'markLazadaOrderAsShippedToWarehouse'])->name('lazada.order.mark_order_as_shipped_to_warehouse');
        Route::post('markLazadaOrderAsPickupConfirmed', [LazadaOrderStatusController::class, 'markLazadaOrderAsPickupConfirmed'])->name('lazada.order.mark_order_as_confirm_pickup');
        Route::post('getLazadaOrdersProcessingNow', [LazadaOrderStatusController::class, 'getLazadaOrdersProcessingNow'])->name('lazada.order.get_lazada_orders_processing_now');
        Route::post('getLazadaOrdersCount', [LazadaOrderStatusController::class, 'getLazadaOrdersCount'])->name('lazada.order.get_lazada_orders_count');

        Route::post('getOrderSyncData', [LazadaOrderSyncController::class, 'getOrderSyncData'])->name('lazada.order.orders_sync_data');
        Route::post('getLazadaShopsForBulkSyncingModal', [LazadaOrderSyncController::class, 'getLazadaShopsForBulkSyncingModal'])->name('lazada.order.shops_for_bulk_order_syncing');

        Route::post('generateLazadaAirwayBillPrint', [LazadaOrderPurchasePdfController::class, 'generateLazadaAirwayBillPrint'])->name('lazada.order.generate_airway_bill_in_bulk');
        Route::get('getDownloadableLazadaBillPdfPrint', [LazadaOrderPurchasePdfController::class, 'getDownloadableLazadaBillPdfPrint'])->name('lazada.order.get_downloadable_airway_bill');
        Route::get('downloadLazadaBillPdfPrint/{token}', [LazadaOrderPurchasePdfController::class, 'downloadLazadaBillPdfPrint'])->name('lazada.order.download_airway_bill');
        Route::post('deleteSpecificLazadaBillPdf', [LazadaOrderPurchasePdfController::class, 'deleteSpecificLazadaBillPdf'])->name('lazada.order.delete_airway_bill');
        Route::post('deleteOldLazadaBillPdf', [LazadaOrderPurchasePdfController::class, 'deleteOldLazadaBillPdf'])->name('lazada.order.delete_all_airway_bill');
        Route::post('checkIfPdfCanBeGenerated', [LazadaOrderPurchasePdfController::class, 'checkIfPdfCanBeGenerated'])->name('lazada.order.can_generate_airway_bill');
        Route::get('downloadSpecificOrderBillPdfFromLazada/{order_id}', [LazadaOrderPurchasePdfController::class, 'downloadSpecificOrderBillPdfFromLazada'])->name('lazada.order.get_specific_order_airway_bill');

        Route::post('getShipmentProviders', [LazadaLogisiticsController::class, 'getShipmentProvidersFromDatabase'])->name('lazada.logistics.get_shipment_providers');

        Route::get('syncOldOrdersFromLazada', [LazadaOrderSyncController::class, 'syncOldOrdersFromLazada'])->name('lazada.order.sync_old_orders');
    });

    Route::group([
        'prefix' => 'linked-catalog'
    ], function () {
        Route::get('datatable', [LazadaLinkedCatalogController::class, 'dataTable'])->name('lazada.linked-catalog.datatable');
        Route::post('store', [LazadaLinkedCatalogController::class, 'store'])->name('lazada.linked-catalog.store');
    });
});

// Facebook
Route::group(['prefix' => 'facebook'], function () {
    Route::get('/', [FacebookController::class, 'index'])->name('facebook.index');
    Route::get('oauth/authorization', [FacebookController::class, 'facebookAuthorization'])->name('facebook.auth');
    Route::get('oauth/redirect', [FacebookController::class, 'authRedirect'])->name('facebook.auth.redirect');
    Route::get('page/list', [FacebookController::class, 'facebookPageList'])->name('facebook.page.list');
    Route::get('page/edit', [FacebookController::class, 'facebookPageEdit'])->name('facebook.page.edit');
    Route::post('page/delete/{id}', [FacebookController::class, 'facebookPageDelete'])->name('facebook.page.delete');
    Route::post('page/autoreply', [FacebookController::class, 'facebookAutoReplyCampaign'])->name('facebook.autoreply.campaign');

    Route::get('page/subscription', [FacebookController::class,'facebookPageSubscribers']);
});

Route::group(['prefix' => 'dodochat'], function() {
    Route::get('new/app', [SettingController::class, 'DoDoChatApp'])->name('dodochat.app');
    Route::get('activity', [SettingController::class, 'dodochat_users_activity'])->name('dodochat.log');
});

Route::group([
    'prefix' => 'sheet-docs',
    'middleware' => 'auth'
], function () {
    Route::get('/', [SheetDocController::class, 'index'])->name('sheet-docs.index');
    Route::post('/', [SheetDocController::class, 'store'])->name('sheet-docs.store');
    Route::get('/datatable', [SheetDocController::class, 'datatable'])->name('sheet-docs.datatable');
    Route::get('/edit/{id}', [SheetDocController::class, 'edit'])->name('sheet-docs.edit');
    Route::post('/update/{id}', [SheetDocController::class, 'update'])->name('sheet-docs.update');
    Route::post('/delete/{id}', [SheetDocController::class, 'delete'])->name('sheet-docs.delete');

    Route::get('/{sheetDoc}/sheet-names', [SheetNameController::class, 'index'])->name('sheet-names.index');
    Route::post('/{sheetDoc}/sheet-names', [SheetNameController::class, 'store'])->name('sheet-names.store');
    Route::get('/{sheetDoc}/sheet-names/datatable', [SheetNameController::class, 'datatable'])->name('sheet-names.datatable');
    Route::get('/{sheetDoc}/sheet-names/edit/{id}', [SheetNameController::class, 'edit'])->name('sheet-names.edit');
    Route::post('/{sheetDoc}/sheet-names/update/{id}', [SheetNameController::class, 'update'])->name('sheet-names.update');
    Route::post('/{sheetDoc}/sheet-names/delete/{id}', [SheetNameController::class, 'delete'])->name('sheet-names.delete');
    Route::post('/{sheetDoc}/sheet-names/sync-now/{id}', [SheetNameSyncNowController::class, 'syncNow'])->name('sheet-names.sync-now');
});

Route::group([
    'prefix' => 'sheet-data-tpks',
    'middleware' => 'auth'
], function () {
    Route::get('/', [SheetDataTpkController::class, 'index'])->name('sheet-data-tpks.index');
    Route::get('/datatable', [SheetDataTpkController::class, 'datatable'])->name('sheet-data-tpks.datatable');
    Route::post('/batch-delete', [SheetDataTpkBatchDeleteController::class, 'batchDelete'])->name('sheet-data-tpks.batch-delete');
    Route::get('/order-analysis', [SheetDataTpkOrderAnalysisController::class, 'index'])->name('sheet-data-tpks.order-analysis');
    Route::get('/order-analysis-datatable', [SheetDataTpkOrderAnalysisController::class, 'datatable'])->name('sheet-data-tpks.order-analysis-datatable');
    Route::get('/order-analysis-chart', [SheetDataTpkOrderAnalysisController::class, 'chart'])->name('sheet-data-tpks.order-analysis-chart');
});

Route::get('checkIfExistsShipmentId', [ShipmentController::class, 'checkIfExistsShipmentId']);
Route::get('checkIfExistsShopeeOrder', [ShopeeOrderPurchaseController::class, 'checkIfExistsShopeeOrder']);

Route::get('shipmentPickOrderCancel', [ShipmentController::class, 'shipmentPickOrderCancel']);
Route::get('shipment_status_update', [ShipmentController::class, 'shipmentStatusUpdate']);
Route::get('after_search_modal_content', [ShipmentController::class, 'afterSearchModalContent']);
Route::get('after_search_modal_content_for_shopee', [ShopeeOrderPurchaseController::class, 'afterSearchModalContentForShopee']);

Route::get('shipmentPickOrderCancelForShopee', [ShopeeOrderPurchaseController::class, 'shipmentPickOrderCancelForShopee']);
Route::get('ShopeeMarkAsShippedCancel', [ShopeeOrderPurchaseController::class, 'ShopeeMarkAsShippedCancel']);
Route::get('get_shopee_ordered_products', [ShopeeOrderPurchaseController::class, 'getShopeeOrderedProducts']);

Route::get('lang/{lang}', LangSwitcherController::class)->name('lang-switcher');
//woo

Route::get('checkIfExistsShipmentIdForWoo', [ShipmentController::class, 'checkIfExistsShipmentIdForWoo']);
Route::get('after_search_modal_content_shipments_products', [ShipmentController::class, 'afterSearchModalContentShipmentsProducts']);

// woo order status
Route::get('woo-data-order-status', [WCOrderPurchaseController::class, 'getWooOrderStatus']);
Route::get('get_woo_shipments_products', [ShipmentController::class, 'getWooShipmentsProducts']);

// edit order dodo
Route::get('getCustomerAddressForOrder', [OrderManageController::class, 'getCustomerAddressForOrder'])->name('shopee.display_customer_address_for_order');
