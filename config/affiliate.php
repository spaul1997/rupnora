<?php

return [
    'attribution_window_days' => (int) env('AFFILIATE_ATTRIBUTION_DAYS', 30),
    'return_hold_days' => (int) env('AFFILIATE_RETURN_HOLD_DAYS', 7),
    'global_commission_rate' => (float) env('AFFILIATE_GLOBAL_RATE', 5),
    'minimum_withdrawal' => (float) env('AFFILIATE_MIN_WITHDRAWAL', 500),
    'queue' => env('AFFILIATE_QUEUE', 'default'),

    /*
     * Keep deductions disabled until an accountant confirms the applicable
     * tax/withholding rules. Supported types: none, percentage, fixed.
     */
    'deduction' => [
        'type' => env('AFFILIATE_DEDUCTION_TYPE', 'none'),
        'value' => (float) env('AFFILIATE_DEDUCTION_VALUE', 0),
    ],
];
