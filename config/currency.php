<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Currency Configuration
    |--------------------------------------------------------------------------
    |
    | Here you can configure the currency settings for your application.
    | You can change the currency symbol, format, and decimal places dynamically.
    |
    */

    'symbol' => 'Rp', // Currency symbol (e.g., $, Rp, €, £)
    'code' => 'IDR', // Currency code (e.g., USD, IDR, EUR, GBP)
    'name' => 'Indonesian Rupiah', // Currency name
    'decimal_places' => 0, // Number of decimal places (0 for no decimals)
    'decimal_separator' => ',', // Separator for decimal part
    'thousands_separator' => '.', // Separator for thousands
    'symbol_first' => true, // Show symbol before amount (true) or after (false)
    'format' => '{symbol} {amount}', // Format template: {symbol}, {amount}, {code}

    /*
    |--------------------------------------------------------------------------
    | Locale Settings
    |--------------------------------------------------------------------------
    |
    | For JavaScript formatting, we use the browser's locale or a specific one.
    | Indonesian locale: 'id-ID'
    | US locale: 'en-US'
    | European locale: 'de-DE'
    |
    */
    'locale' => 'id-ID', // Default locale for number formatting

];
