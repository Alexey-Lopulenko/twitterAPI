<?php
$paramFromFilter = [
    'name'=>'lol',
    'university'=>'lol2',
    'abbreviate'=> 'lol3'
];

//foreach ($paramFromFilter as $value){
//    echo $value.'</br>';
//}

$test = 'test team players';

$test2 = ' yers ';

if(stristr($test, $test2)){
    echo 'est';
}else{
    echo 'net';
}