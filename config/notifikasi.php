<?php

return [

    /*
    |--------------------------------------------------------------------------
    | WhatsApp Driver
    |--------------------------------------------------------------------------
    |
    | Supported now: "log" (stub). Real drivers (cloud_api, gateway, chatcepat)
    | are added later — see app/Services/Whatsapp.
    |
    */
    'wa' => [
        'driver' => env('WA_DRIVER', 'log'),
        'sender' => env('WA_SENDER'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Reminder Schedule
    |--------------------------------------------------------------------------
    |
    | Days relative to a bill's due date that trigger reminders. Overdue days
    | can be overridden at runtime by the "reminder_overdue_hari" setting.
    |
    */
    'reminder' => [
        'h3_days_before' => 3,
        'overdue_days_after' => [1, 3, 7],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Channels
    |--------------------------------------------------------------------------
    */
    'channels' => ['whatsapp', 'email'],

];
