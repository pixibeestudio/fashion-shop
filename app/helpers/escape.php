<?php

/**
 * Escape HTML special characters to prevent XSS.
 * 
 * @param string|null $value
 * @return string
 */
function e($value) {
    if (is_null($value)) return '';
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/**
 * Format price to VND currency.
 * 
 * @param float|int $amount
 * @return string
 */
function formatPrice($amount) {
    return number_format((float)$amount, 0, ',', '.') . ' ₫';
}
