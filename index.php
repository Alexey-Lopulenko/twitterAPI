<?php
require_once('setting_db.php');

error_reporting(E_ALL);
ini_set('display_errors',true);

$paramFromFilter = [
    'name'=>'lol',
    'university'=>'lol2',
    'abbreviate'=> 'lol3'
];

//foreach ($paramFromFilter as $value){
//    echo $value.'</br>';
//}

//$test = 'test team players';
//
//$test2 = ' yers ';
//
//if(stristr($test, $test2)){
//    echo 'est';
//}else{
//    echo 'net';
//}
//$id = 1;
//$twitter_parsed = 0;

//$sql = "UPDATE players SET twitter_parsed=?, WHERE id=?";
//$stmt= $pdo->prepare($sql);
//if($stmt->execute([$twitter_parsed, $id])){
//    echo 'good';
//}else{
//    echo 'false';
//}

//$query = "UPDATE players SET twitter_parsed ='$twitter_parsed' WHERE id = :id";
//$stmt = $pdo->prepare($query);
//$stmt->BindValue(':id', $id, PDO::PARAM_INT);
//$stmt->execute();

//$string = "Academy of Art University";
//$string = trim($string);
//
//if(strpos($string,',')) $string=substrBetween($string,'',',');
//
//$abbreviation = "";
//
//$words = explode(" ", "$string");
//foreach ($words as $word) {
//
//
//    if($word[0]=='(' || ctype_lower($word[0])===true) $word='';
//
//    $word = ucwords($word);
//
//    if($word != "") {
//        $abbreviation .= $word[0];
//    }
//}
//
//var_dump($abbreviation);