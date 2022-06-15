<?php

return [
    /**
     * Service account credentials
     * Create a new service account on the Google API console
     * Then download the credentials.json file and place it in the storage/app/google-sheet/ folder
     */
    'service_account_json' => storage_path('app/google-sheet/credentials.json'),

    /**
     * See the catalogue of the scopes at https://developers.google.com/identity/protocols/oauth2/scopes
     * Make sure you already have the scope enabled on your Google API console
     */
    'scopes' => [
        // 'https://www.googleapis.com/auth/drive',
        // 'https://www.googleapis.com/auth/drive.file',
        // 'https://www.googleapis.com/auth/drive.readonly',
        'https://www.googleapis.com/auth/spreadsheets',
        // 'https://www.googleapis.com/auth/spreadsheets.readonly',
    ]
];