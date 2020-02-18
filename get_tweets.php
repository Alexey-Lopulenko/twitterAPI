<?php
// include config and twitter api wrappe
require_once('config.php');
require_once('setting_db.php');
require_once('TwitterAPIExchange.php');
require_once('MyClass.php');
set_time_limit(0);

$idPlayer = 1;



error_reporting(E_ALL);
ini_set('display_errors',true);
$institutions = $pdo->query("SELECT id FROM institutions ORDER BY id ASC")->fetchAll();

$data = $pdo->prepare("
SELECT players.id, players.name , teams.team , teams.university , players.high_school
FROM players
INNER JOIN teams 
ON players.institution_id = teams.institution_id 
AND players.team_id = teams.p_team_id
AND players.twitter_parsed != 1
WHERE  players.institution_id = :institution_id
");

$settings = array(
    'oauth_access_token' => TWITTER_ACCESS_TOKEN,
    'oauth_access_token_secret' => TWITTER_ACCESS_TOKEN_SECRET,
    'consumer_key' => TWITTER_CONSUMER_KEY,
    'consumer_secret' => TWITTER_CONSUMER_SECRET
);

//обработка игроков в рамках одного института
foreach ($institutions as $institution){
    $data->execute(['institution_id' => $institution['id']]);
    $row = $data->fetchAll(PDO::FETCH_OBJ);
    if(!empty($row)){
        //обработка всего списка поиска
        foreach ($row as $value){
            $arrUserData = (array) $value;
            $ttrE = new MyClass($settings);
            $searchList = $ttrE ->searchUser($arrUserData['name']);

            if(!empty($searchList)){
                $paramFromFilter = [
                    'university'=>$arrUserData['university'],
                    'abbreviate'=>$ttrE->abbreviate($arrUserData['university']),
                    'high_school'=>$arrUserData['high_school'],
                    'team'=>$arrUserData['team'],
                ];
                $arrSuitableUsersByDescription = $ttrE->filterUsersByDescription($searchList, $paramFromFilter);
                echo '<hr><h1>Description</h1>';

                //перебор юзеров которые подошли по description и запись их в базу
                foreach ($arrSuitableUsersByDescription as $user){

                    $arrTweets = $ttrE->getUserTweets($user['screen_name']);
                    $statistics = $ttrE->getTweetStatistics($arrTweets);
                    $urlRecentTweet = 'https://twitter.com/'.$user['screen_name'].'/status/'.$statistics['recentTweetId'];

                    echo '<hr>';
                    var_dump($arrUserData['id']);
                    var_dump($user['url']);
                    var_dump( $user['count_tweets']);
                    var_dump( $user['count_followers']);
                    var_dump($user['img']);
                    var_dump($statistics['retweets_tweet']);
                    var_dump($statistics['like_tweet']);
                    var_dump($urlRecentTweet);
                    echo '<hr>';

//                    $sql = "
//INSERT INTO
//players_twitter_data (player_id, twitter_url, count_tweets, count_followers, profile_image, retweets_tweet,likes_tweet, recent_post)
//VALUES (?,?,?,?,?,?,?,?)";
//                    if(
//                    $pdo->prepare($sql)->execute([
//                        $arrUserData['id'],
//                        $user['url'],
//                        $user['count_tweets'],
//                        $user['count_followers'],
//                        $user['img'],
//                        $statistics['retweets_tweet'],
//                        $statistics['like_tweet'],
//                        $urlRecentTweet
//                    ])){
//                        echo 'good<br>';
//                    }else{
//                        echo 'false';die;
//                    }
                }


                echo '<h1>Tweets</h1>';
                ////filter by tweet data(text)
                foreach ($arrSuitableUsersByDescription as $suitableUserData){
                    foreach ($searchList as $userData){

                        if($suitableUserData['screen_name'] != $userData['screen_name']){
                            $arrTweets = $ttrE->getUserTweets($userData['screen_name']);
                            if($ttrE->filterUsersByTweetData($arrTweets, $paramFromFilter)){
                                $url_profile = "https://twitter.com/" . $userData['screen_name'];
                                $profileImg = str_replace("normal", "400x400", $userData['profile_image_url_https']);
                                $arrTweets = $ttrE->getUserTweets($userData['screen_name']);
                                $statistics = $ttrE->getTweetStatistics($arrTweets);
                                $urlRecentTweet = 'https://twitter.com/'.$userData['screen_name'].'/status/'.$statistics['recentTweetId'];


                                echo '<hr>';
                               var_dump($arrUserData['id']);
                               var_dump($url_profile);
                               var_dump($userData['statuses_count']);
                               var_dump($userData['followers_count']);
                               var_dump($profileImg);
                               var_dump($statistics['retweets_tweet']);
                               var_dump($statistics['like_tweet']);
                               var_dump($urlRecentTweet);
                                echo '<hr>';

                                //save in database
//                                $sql = "
//INSERT INTO
//players_twitter_data (player_id, twitter_url, count_tweets, count_followers, profile_image, retweets_tweet,likes_tweet, recent_post)
//VALUES (?,?,?,?,?,?,?,?)";
//                                //проверка сохранения в базу данных
//                                if(
//                                $pdo->prepare($sql)->execute([
//                                    $arrUserData['id'],
//                                    $url_profile,
//                                    $userData['statuses_count'],
//                                    $userData['followers_count'],
//                                    $profileImg,
//                                    $statistics['retweets_tweet'],
//                                    $statistics['like_tweet'],
//                                    $urlRecentTweet
//                                ])){
//                                    echo 'good<br>';
//                                }else{
//                                    echo 'error save in database! <br>';die();
//                                }
                            }
                        }

                    }
                }


            }
die;
            //update twitter_parsed set status = 1
//            $statusPars = 1;
//            $query = "UPDATE players SET twitter_parsed ='$statusPars' WHERE id = :id";
//            $stmt = $pdo->prepare($query);
//            $stmt->BindValue(':id', $arrUserData['id'], PDO::PARAM_INT);
//            $stmt->execute();
        }
        die;
    }

}
/////////////////////////////////////////////////////////////
//die;
//
//echo '<h2>Data in db</h2>';
//echo '<strong>name: </strong>'.$row[0]['name'].'<br>';
//echo '<strong>team: </strong>'.$row[0]['team'].'<br>';
//echo '<strong>high_school: </strong>'.$row[0]['high_school'].'<br>';
//echo '<strong>university: </strong>'.$row[0]['university'].'<br>';
//
////die;
//// settings for twitter api connection
//$settings = array(
//    'oauth_access_token' => TWITTER_ACCESS_TOKEN,
//    'oauth_access_token_secret' => TWITTER_ACCESS_TOKEN_SECRET,
//    'consumer_key' => TWITTER_CONSUMER_KEY,
//    'consumer_secret' => TWITTER_CONSUMER_SECRET
//);
?>
<!--<h1>Twitter</h1>-->
<?php
//$ttrE = new MyClass($settings);
//$searchList = $ttrE ->searchUser($row[0]['name']);
//$paramFromFilter = [
////        'name'=>$row[0]['name'],
//        'university'=>$row[0]['university'],
//        'abbreviate'=>$ttrE->abbreviate($row[0]['university']),
//        'high_school'=>$row[0]['high_school'],
//        'team'=>$row[0]['team'],
//];
//
//
//$arrSuitableUsersByDescription = $ttrE->filterUsersByDescription($searchList, $paramFromFilter);
//echo '<hr><h2>Description</h2>';
//var_dump($arrSuitableUsersByDescription);
////echo '<hr><h2>post</h2>';
//foreach ($arrSuitableUsersByDescription as $user){
//
//    $arrTweets = $ttrE->getUserTweets($user['screen_name']);
//    $statistics = $ttrE->getTweetStatistics($arrTweets);
//    $urlRecentTweet = 'https://twitter.com/'.$user['screen_name'].'/status/'.$statistics['recentTweetId'];
//
//    $sql = "
//INSERT INTO
//players_twitter_data (player_id, twitter_url, count_tweets, count_followers, profile_image, retweets_tweet,likes_tweet, recent_post)
//VALUES (?,?,?,?,?,?,?,?)";
//    if(
//    $pdo->prepare($sql)->execute([
//        $idPlayer,
//        $user['url'],
//        $user['count_tweets'],
//        $user['count_followers'],
//        $user['img'],
//        $statistics['retweets_tweet'],
//        $statistics['like_tweet'],
//        $urlRecentTweet
//    ])){
//        echo 'good<br>';
//    }else{
//        echo 'false';die;
//    }
//}
//
//
////filter users by tweet data
//foreach ($arrSuitableUsersByDescription as $suitableUserData){
//    foreach ($searchList as $userData){
//
//        if($suitableUserData['screen_name'] != $userData['screen_name']){
//            $arrTweets = $ttrE->getUserTweets($userData['screen_name']);
//            if($ttrE->filterUsersByTweetData($arrTweets, $paramFromFilter)){
//                $url_profile = "https://twitter.com/" . $userData['screen_name'];
//                $profileImg = str_replace("normal", "400x400", $userData['profile_image_url_https']);
//                $arrTweets = $ttrE->getUserTweets($userData['screen_name']);
//                $statistics = $ttrE->getTweetStatistics($arrTweets);
//
//
//                echo "screen_name ".$userData['screen_name']."<br>";
//                echo "count_tweets ".$userData['statuses_count']."<br>";
//                echo "count_followers ".$userData['followers_count']."<br>";
//                echo "count_followers ".$profileImg."<br>";
//
//                //save in database
//                $sql = "
//INSERT INTO
//players_twitter_data (player_id, twitter_url, count_tweets, count_followers, profile_image, retweets_tweet,likes_tweet, recent_post)
//VALUES (?,?,?,?,?,?,?,?)";
//                if(
//                $pdo->prepare($sql)->execute([
//                    $idPlayer,
//                    $url_profile,
//                    $userData['statuses_count'],
//                    $userData['followers_count'],
//                    $profileImg,
//                    $statistics['retweets_tweet'],
//                    $statistics['like_tweet'],
//                    $urlRecentTweet
//                ])){
//                    echo 'good<br>';
//                }else{
//                    echo 'false';die;
//                }
//            }
//        }
//
//    }
//}
//die;?>
