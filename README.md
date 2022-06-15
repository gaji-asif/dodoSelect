<p align="center"><a href="https://dev.dodoselect.com" target="_blank"><img src="https://dev.dodoselect.com/img/dodoselect.png"></a></p>

## About DoDo Select

<strong>DoDo Select</strong> is a web application developed using laravel framework. We believe managing products and inventory must be an enjoyable and creative experience to be truly fulfilling. <strong>DoDo Select</strong> takes the pain out of management by connecting it to multiple channels like:

- WP WooCommerce
- Lazada
- Shopee
- Facebook Autoreply and many more to be added

<strong>DoDo Select</strong> is accessible, powerful, and provides tools required for large, reliable, robust inventory management.
## Queue flush
- Execute these commands to clear and restart queues
  - `service supervisor stop`
  - `redis-cli flushall`
  - `service supervisor start`
## Installation
 - Git pull from the repository
 - Write config settings on `.env` file
    - `QUEUE_CONNECTION = 'redis'`
    - `REDIS_CLIENT = 'predis' or 'phpredis'`
 - Change permission to `777` for `storage` directory
   - Paths needed: 
     - `storage/app/shopee/airway_bills`
     - `storage/app/lazada/airway_bills`
 - Execute `composer install`
 - Run migration `php artisan migrate`
 - Install `wkhtmltopdf`
 - Install `GhostScript`
 - Install `apt install libthai0 xfonts-thai`

## Environmental requirements
- Install `redis`, `supervisor`, `phpredis`
    - If `phpredis` not possible, install `predis` using composer
- Supervisor config files
    - horizon[`.ini` (Debian)/`.conf` (centOS)] worker config
  ```shell script
        [program:horizon]
        process_name=%(program_name)s
        command=php (path/to/project)/artisan horizon
        autostart=true
        autorestart=true
        redirect_stderr=true
        stdout_logfile=(path/to/project)/storage/horizon.log
        stopwaitsecs=3600
  ```
  - queue[`.ini` (Debian)/`.conf` (centOS)] worker config
  ```shell script
  [program:dodo-worker]
  process_name=%(program_name)s_%(process_num)02d
  command=php (path/to/project)/artisan queue:work
  autostart=true
  autorestart=true
  stopasgroup=true
  killasgroup=true
  numprocs=1
  redirect_stderr=true
  stdout_logfile=(path/to/project)/storage/worker.log
  stopwaitsecs=3600
  ```
## Modules

- [**1**](). Product
    - [**1.1**](). List
        - [**1.1.1**]() Edit
        - [**1.1.2**]() Delete
        - [**1.1.3**]() Stock Adjust
        - [**1.1.4**]() Inventory Sync
    - [**1.2**]() Stock Adjust
        - [**1.2.1**]() Adjustment
        - [**1.2.2**]() History
        - [**1.2.3**]() Defect Stock
- [**2**](). Order
    - [**2.1**](). Purchase Order
- [**3**](). Shipment
    - [**3.1**](). Manage Shipper
- [**4**](). CRM
    - [**4.1**]() Customers
- [**5**](). WooCommerce
    - [**5.1**]() Order
    - [**5.2**]() Product
        - [**5.2.1**]() Sync Selected
        - [**5.2.2**]() Sync Product
    - [**5.3**]() Inventory
- [**6**](). Report 
    - [**6.1**]() Stock
    - [**6.2**]() Stock movements
    - [**6.3**]() Activity Log
- [**7**](). Settings
    - [**7.1**]() Categories
    - [**7.2**]() Suppliers
    - [**7.3**]() Shops
    - [**7.4**]() Channels
    - [**7.5**]() Exchange Rate
    - [**7.6**]() Product Tag
    - [**7.7**]() Ship Types
    - [**7.8**]() Woo Settings
    - [**7.9**]() Cron Reports
- [**8**](). Facebook 
    - [**8.1**]() Authorize Facebook Pages
    - [**8.1**]() Manage Pages (For autoreply)
## License

Copyright &copy; All rights reserved by <a href="https://www.acplusglobal.com">AC Plus Global Co., Ltd</a>.
