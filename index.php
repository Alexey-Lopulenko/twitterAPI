<?php
//require_once('config.php');
//require_once('setting_db.php');
//require_once('TwitterAPIExchange.php');
//require_once('MyClass.php');
//
//$settings = array(
//    'oauth_access_token' => TWITTER_ACCESS_TOKEN,
//    'oauth_access_token_secret' => TWITTER_ACCESS_TOKEN_SECRET,
//    'consumer_key' => TWITTER_CONSUMER_KEY,
//    'consumer_secret' => TWITTER_CONSUMER_SECRET
//);
//
//
//$ttrw = new MyClass($settings);
//$limit = $ttrw->getLimits();
//var_dump($limit['resources']);

//$searchList = $ttrw->searchUser('Лопуленко');
//var_dump($searchList);

$contFoDate = "
							Campus /
							Featured /
							News /
			 February 19, 2020";
//preg_match_all('%(?<=\/(?!.*\/)\s)([\s\S]+?)(?=$)%', $contFoDate, $textDd);


preg_match_all('%(?<=\/(?!.*\/)\s)([\s\S]+?)(?=$)%', $contFoDate, $textDd);var_dump($textDd[0][0]);
$date=date('F d Y', strtotime($textDd[0][0]));
var_dump($date);