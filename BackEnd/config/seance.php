<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Seance Week Convention
    |--------------------------------------------------------------------------
    |
    | Keep one shared convention for all seance/schedule flows.
    | V1 uses Sunday as the first day of the week.
    |
    */
    'week_starts_on' => 0, // 0=Sunday, 1=Monday, ... 6=Saturday

    /*
    |--------------------------------------------------------------------------
    | Weekday Mapping
    |--------------------------------------------------------------------------
    |
    | Canonical integer mapping used in API payloads and DB values.
    |
    */
    'weekday_map' => [
        0 => 'sunday',
        1 => 'monday',
        2 => 'tuesday',
        3 => 'wednesday',
        4 => 'thursday',
        5 => 'friday',
        6 => 'saturday',
    ],

    /*
    |--------------------------------------------------------------------------
    | Attendance / Memorization Behavior
    |--------------------------------------------------------------------------
    |
    | If true: creating/updating memorization auto-creates "present" attendance
    | when no attendance record exists yet for that student in that seance.
    |
    */
    'auto_mark_present_on_memorization' => true,
];
