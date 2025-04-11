<?php

/**
 * Format a number as currency.
 *
 * @param float $amount
 * @return string
 */
function currency_format($amount)
{
    return '$' . number_format($amount, 2);
} 