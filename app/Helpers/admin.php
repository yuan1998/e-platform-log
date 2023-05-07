<?php

function generateRandomString($length = 10)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function validateDate($date, $format = 'Y-m-d')
{
    $d = DateTime::createFromFormat($format, $date);
    // The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
    return $d && $d->format($format) === $date;
}

function greetOfNow($date = null)
{
    $date = $date ?: now();
    $hour = $date->format('H');
    if ($hour < 12) {
        return 1;
    }
    if ($hour < 18) {
        return 2;
    }
    return 3;
}

function greetOfNowCn($date = null)
{
    $r = greetOfNow($date);
    $l = [
        1 => '早上',
        2 => '下午',
        3 => '晚上',
    ];
    return data_get($l, $r);
}
