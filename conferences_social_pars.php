<?php
// include config and twitter api wrappe
require_once('config.php');
require_once('setting_db.php');
require_once('TwitterAPIExchange.php');
require_once('MyClass.php');
set_time_limit(0);


error_reporting(E_ALL);
ini_set('display_errors', true);

$conferences = $pdo->query("SELECT * FROM conference ")->fetchAll();

$settings = array(
    'oauth_access_token' => TWITTER_ACCESS_TOKEN,
    'oauth_access_token_secret' => TWITTER_ACCESS_TOKEN_SECRET,
    'consumer_key' => TWITTER_CONSUMER_KEY,
    'consumer_secret' => TWITTER_CONSUMER_SECRET
);
$conf_social = new MyClass($settings);


foreach ($conferences as $conference){
//    var_dump($conference['org_id']);die;

        $twitter_url = $pdo->query("SELECT `twitter_url` FROM conference_social  WHERE (conference_org_id = {$conference['org_id']}) ORDER BY id ASC")->fetchAll();
        if($twitter_url[0][0]){
            var_dump($twitter_url[0][0]);
            $name_group = explode('/',$twitter_url[0][0]);
            $name_group = array_reverse($name_group);

            echo '<hr>';
            $groupInfo = $conf_social->getUserInfo($name_group[0]);

            var_dump($groupInfo);

            $sql = "
INSERT INTO
conference_twitter   (conference_org_id, twitter_url, count_tweets, count_followers, profile_image, retweets_tweet,likes_tweet, recent_post)
VALUES (?,?,?,?,?,?,?,?)";

            if($pdo->prepare($sql)->execute([
                $conference['org_id'],
                $twitter_url[0][0],
                $groupInfo['count_tweet'],
                $groupInfo['count_followers'],
                $groupInfo['profile_image'],
                $groupInfo['retweets_tweet'],
                $groupInfo['likes_tweet'],
                $groupInfo['recent_post'],
            ])){
                $res = 'good';
                var_dump($res);
            }else{
                $res ='false';
                var_dump($res);
            }


        }

}