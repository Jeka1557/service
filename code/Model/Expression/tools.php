<?php
/**
 * Created by PhpStorm.
 * User: Jeka
 * Date: 27.01.2019
 * Time: 21:50
 */


function countWords($text) {
    return mb_strlen($text);
}


function countLetters($text) {
    $words = preg_split('/[\s.,\n-]+/u', $text);

    $c = 0;

    foreach ($words as $word) {
        $word = trim($word, " \t\n\r\0\x0B\"'");

        if (strlen($word))
            $c += 1;
    }

    return $c;
}


function dateAdd(\DateTime $date, \DateInterval $interval) {
    return $date->add($interval);
}

function dateSub(\DateTime $date, \DateInterval $interval) {
    return $date->sub($interval);
}

function intervalDays($days) {
    return new \DateInterval('P'.$days.'D');
}

function intervalMonths($days) {
    return new \DateInterval('P'.$days.'M');
}

function intervalYears($days) {
    return new \DateInterval('P'.$days.'Y');
}


function daysInMonth(\DateTime $date) {
    $days = cal_days_in_month(CAL_GREGORIAN, (int)$date->format('m'), (int)$date->format('Y'));
    return $days;
}

function daysInYear($date) {
    $days = 0;

    for ($m=1; $m<=12; $m++)
        $days += cal_days_in_month(CAL_GREGORIAN, $m, (int)$date->format('Y'));

    return $days;
}

function dateCurrent() {
    return new DateTime(date('Y-m-d'));
}


function age(DateTime $birthday) {
    $current = dateCurrent();
    $diff  = $current->diff($birthday);
    return $diff->y;
}

function isAcademicYear(DateTime $date) {
    $year = $date->format('Y');

    $dateStart = new DateTime($year.'-09-01');
    $dateEnd = new DateTime($year.'-06-01');

    if ($date>$dateStart or $date<$dateEnd)
        return true;
    else
        return false;
}


function limitNumber($value, $min = null, $max = null) {

    if (!is_null($min) and ($value<$min))
        return $min;

    if (!is_null($max) and ($value>$max))
        return $max;

    return $value;
}