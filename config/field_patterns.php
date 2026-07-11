<?php

/**
 * Locale-specific field validation patterns.
 *
 * These are system-level configuration — NOT tenant-overridable.
 * A tenant cannot change the postal code format for a country.
 *
 * Keys match field keys from industry configs (construcao_civil.php, etc.).
 * Patterns use PHP regex syntax (without delimiters).
 */

return [

    'pt' => [
        'postal_code' => '^\d{4}-\d{3}$',
    ],

    'en' => [
        'postal_code' => '^\d{5}(-\d{4})?$',
    ],

];
