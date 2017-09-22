<?php

return [

    'charsets' => [
        'num' => '0123456789',
        'vowel' => 'aeijoy',
        'consonant' => 'bcdfghklmnpqrstvwxyz',
        'special' => 'áäâàæãåāaeéëêèęėēuúüûùūiíïïìiîįīoóöôòõœøōç',
        'space' => ' ',
        'dash' => '-',
        'underscore' => '_',
        'punctuation' => ':,.?!()',
    ],

    'aliases' => [
        'lowercase' => "vowel|consonant",
        'uppercase' => "strtoupper:lowercase",
        'alpha' => "lowercase|uppercase",
        'string' => "alpha|num|dash|space|underscore|punctuation|'&$@'",
        'hash' => "alpha|num|dash|underscore|'&$@'",
    ],

];