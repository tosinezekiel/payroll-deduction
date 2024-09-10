<?php

if (!function_exists('to_cents')) {
    /**
     * Convert dollar to cents.
     *
     * @param  float|int $value Amount in dollars.
     * @return int Amount in cents.
     */
    function to_cents($value) {
        return (int) round($value * 100); 
    }
}

if (!function_exists('to_usd')) {
    /**
     * Convert cents to dollars.
     *
     * @param  int $value Amount in cents.
     * @return float Amount in dollars.
     */
    function to_usd($value) {
        return $value / 100;  
    }
}