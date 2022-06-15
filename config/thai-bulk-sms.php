<?php

return [
    /**
     * API Key
     */
    'api_key' => env('THAI_BULK_API_KEY', ''),

    /**
     * API Secret Key
     */
    'secret_key' => env('THAI_BULK_SECRET_KEY', ''),

    /**
     * Set sender name
     * https://member.thaibulksms.com/sendername
     *
     * Default value is "SMS" or "Demo" or "MySMS"
     */
    'sender' => env('THAI_BULK_SENDER', 'SMS')
];