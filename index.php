<?php

$string ='Alou ioio IIo Rre';
$string = trim($string);

if(strpos($string,',')) $string=substrBetween($string,'',',');

$abbreviation = "";

$words = explode(" ", "$string");
foreach ($words as $word) {


    if ($word[0] == '(' || ctype_lower($word[0]) === true) $word = '';

    $word = ucwords($word);

    if ($word != "") {
        $abbreviation .= $word[0];
    }
}
echo $abbreviation;