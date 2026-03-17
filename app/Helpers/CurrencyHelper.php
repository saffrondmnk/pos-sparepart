<?php

if (!function_exists('format_currency')) {
    /**
     * Format a number as currency based on config settings.
     *
     * @param float $amount
     * @param int|null $decimalPlaces
     * @return string
     */
    function format_currency($amount, $decimalPlaces = null)
    {
        $config = config('currency');
        
        if ($decimalPlaces === null) {
            $decimalPlaces = $config['decimal_places'];
        }
        
        $symbol = $config['symbol'];
        $decimalSeparator = $config['decimal_separator'];
        $thousandsSeparator = $config['thousands_separator'];
        $symbolFirst = $config['symbol_first'];
        
        $formattedAmount = number_format(
            $amount,
            $decimalPlaces,
            $decimalSeparator,
            $thousandsSeparator
        );
        
        if ($symbolFirst) {
            return $symbol . ' ' . $formattedAmount;
        } else {
            return $formattedAmount . ' ' . $symbol;
        }
    }
}

if (!function_exists('format_currency_simple')) {
    /**
     * Simple currency formatting without symbol (for JavaScript generation).
     *
     * @param float $amount
     * @param int|null $decimalPlaces
     * @return string
     */
    function format_currency_simple($amount, $decimalPlaces = null)
    {
        $config = config('currency');
        
        if ($decimalPlaces === null) {
            $decimalPlaces = $config['decimal_places'];
        }
        
        $decimalSeparator = $config['decimal_separator'];
        $thousandsSeparator = $config['thousands_separator'];
        
        return number_format(
            $amount,
            $decimalPlaces,
            $decimalSeparator,
            $thousandsSeparator
        );
    }
}
