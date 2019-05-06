<?php
/**
 * Settings for each channel, you may declare your default settings in rabbit.php default section, if you have the same settings for
 * all channels
 */
return [
    'ICC_LINK_SAVER' => [
        'queue' => 'ICC_LINK_SAVER',
        'batch' => 5
    ],
    'ICC_BUSINESS_SAVER' => [
        'queue' => 'ICC_BUSINESS_SAVER',
        'batch' => 5
    ]
];
