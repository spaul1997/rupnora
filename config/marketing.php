<?php

return [
    'cc_email' => env('MARKETING_CC_EMAIL', 'rupnorafashion@gmail.com'),
    'queue' => env('MARKETING_QUEUE', 'emails'),
    'max_recipients_per_campaign' => (int) env('MARKETING_MAX_RECIPIENTS', 2000),
];
