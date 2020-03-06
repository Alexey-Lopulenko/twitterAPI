<?php
// include config and twitter api wrappe
require_once('config.php');
require_once('setting_db.php');
require_once('TwitterAPIExchange.php');
require_once('MyClass.php');
set_time_limit(0);

//$idPlayer = 1;


error_reporting(E_ALL);
ini_set('display_errors', true);
$startInstitution = $argv[1];
$endInstitution = $argv[2];

$institutions = $pdo->query("SELECT id FROM institutions WHERE (id >= {$startInstitution} and id <={$endInstitution}) ORDER BY id ASC")->fetchAll();

$data = $pdo->prepare("
SELECT DISTINCT players.id, players.name , teams.team , teams.university , players.high_school
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
$ttrE = new MyClass($settings);
$limit = $ttrE->getLimits();
//var_dump($limit['resources']);die;
//обработка игроков в рамках одного института
foreach ($institutions as $institution) {
    $data->execute(['institution_id' => $institution['id']]);
    var_dump($institution['id']);
    $row = $data->fetchAll(PDO::FETCH_OBJ);
    if (!empty($row)) {
//        var_dump($row);
//        die;
        try {
            //обработка всего списка поиска
            foreach ($row as $value) {

                $arrUserData = (array)$value;
                if (!$arrUserData['name']){
                    continue;
                }
                $searchList = $ttrE->searchUser($arrUserData['name']);

                if (!empty($searchList)) {
                    $paramFromFilter = [
                        'university' => $arrUserData['university'],
                        'abbreviate' => $ttrE->abbreviate($arrUserData['university']),
                        'high_school' => $arrUserData['high_school'],
                        'team' => $arrUserData['team'],
                    ];

                    //use first 50 users by filter from description
                    $searchList = array_slice($searchList, 0, 50);
                    $arrSuitableUsersByDescription = $ttrE->filterUsersByDescription($searchList, $paramFromFilter);


                    //перебор юзеров которые подошли по description и запись их в базу
                    if (!empty($arrSuitableUsersByDescription)) {
//                                        var_dump($arrSuitableUsersByDescription);die;
                        foreach ($arrSuitableUsersByDescription as $user) {

                            $arrTweets = $ttrE->getUserTweets($user['screen_name']);
                            $statistics = $ttrE->getTweetStatistics($arrTweets);
                            if ($statistics['recentTweetId']) {
                                $urlRecentTweet = 'https://twitter.com/' . $user['screen_name'] . '/status/' . $statistics['recentTweetId'];
                            } else {
                                $urlRecentTweet = 'no tweet';
                            }


                            $sql = "
INSERT INTO
players_twitter_data (player_id, twitter_url, count_tweets, count_followers, profile_image, retweets_tweet,likes_tweet, recent_post)
VALUES (?,?,?,?,?,?,?,?)";
                            $twitterUrl = $pdo->query("SELECT twitter_url FROM players_twitter_data WHERE twitter_url LIKE '%{$user['url']}%'")->fetchAll();

                            if (count($twitterUrl) == 0) {
                                if (
                                $pdo->prepare($sql)->execute([
                                    $arrUserData['id'],
                                    $user['url'],
                                    $user['count_tweets'],
                                    $user['count_followers'],
                                    $user['img'],
                                    $statistics['retweets_tweet'],
                                    $statistics['like_tweet'],
                                    $urlRecentTweet
                                ])) {
                                    echo 'good<br>';
                                } else {
                                    $message = "Error - not save:\nplayerID=>" . $arrUserData['id'] .
                                        "\nuserURL=>" . $user['url'] .
                                        "\ncountTweet=>" . $user['count_tweets'] .
                                        "\ncount_followers=>" . $user['count_followers'] .
                                        "\nimg=>" . $user['img'] .
                                        "\nretweets_tweet=>" . $statistics['retweets_tweet'] .
                                        "\nlike_tweet=>" . $statistics['like_tweet'] .
                                        "\nurlRecentTweet=>" . $urlRecentTweet;
                                    $ttrE->sendReportToTelegram($message);
                                }
                            }

                        }
                    }


                    ////filter by tweet data(text)
                    foreach ($arrSuitableUsersByDescription as $suitableUserData) {
                        $searchList = array_slice($searchList, 0, 50);
                        foreach ($searchList as $userData) {
                            if (array_key_exists('screen_name', $userData) && array_key_exists('screen_name', $suitableUserData)) {
                                if ($suitableUserData['screen_name'] != $userData['screen_name']) {
                                    $arrTweets = $ttrE->getUserTweets($userData['screen_name']);
                                    if (!empty($arrTweets)) {
                                        if ($ttrE->filterUsersByTweetData($arrTweets, $paramFromFilter)) {
                                            $url_profile = "https://twitter.com/" . $userData['screen_name'];
                                            $profileImg = str_replace("normal", "400x400", $userData['profile_image_url_https']);
                                            $arrTweets = $ttrE->getUserTweets($userData['screen_name']);
                                            $statistics = $ttrE->getTweetStatistics($arrTweets);
                                            $urlRecentTweet = 'https://twitter.com/' . $userData['screen_name'] . '/status/' . $statistics['recentTweetId'];


                                            //save in database
                                            $sql = "
INSERT INTO
players_twitter_data (player_id, twitter_url, count_tweets, count_followers, profile_image, retweets_tweet,likes_tweet, recent_post)
VALUES (?,?,?,?,?,?,?,?)";

                                            $twitterUrl = $pdo->query(
                                                "SELECT twitter_url FROM players_twitter_data WHERE twitter_url LIKE '%{$url_profile}%'")->fetchAll();

                                            if (count($twitterUrl) == 0) {
                                                //проверка сохранения в базу данных
                                                if (
                                                $pdo->prepare($sql)->execute([
                                                    $arrUserData['id'],
                                                    $url_profile,
                                                    $userData['statuses_count'],
                                                    $userData['followers_count'],
                                                    $profileImg,
                                                    $statistics['retweets_tweet'],
                                                    $statistics['like_tweet'],
                                                    $urlRecentTweet
                                                ])) {
                                                    echo 'good<br>';
                                                } else {
                                                    $message = "Error - not save:\n playerID=>" . $arrUserData['id'] .
                                                        "\n userURL=>" . $url_profile .
                                                        "\ncountTweet=>" . $userData['statuses_count'] .
                                                        "\ncount_followers=>" . $userData['followers_count'] .
                                                        "\nimg=>" . $profileImg .
                                                        "\nretweets_tweet" . $statistics['retweets_tweet'] .
                                                        "\nlike_tweet=>" . $statistics['like_tweet'] .
                                                        "\nurlRecentTweet=>" . $urlRecentTweet;
                                                    $ttrE->sendReportToTelegram($message);
                                                }
                                            }
                                        }
                                    }

                                }
                            }


                        }
                    }


                }
//die;
                //update twitter_parsed set status = 1
                $statusPars = 1;
                $query = "UPDATE players SET twitter_parsed ='$statusPars' WHERE id = :id";
                $stmt = $pdo->prepare($query);
                $stmt->BindValue(':id', $arrUserData['id'], PDO::PARAM_INT);
                $stmt->execute();
            }
            $message = "Parse \n InstitutionID => " . $institution['id'];
            $ttrE->sendReportToTelegram($message);
        } catch (Exception $e) {
            $message = $e->getMessage();
            $ttrE->sendReportToTelegram($message);
            if ($message == 'CURLOPT_TIMEOUT') {
                `php get_tweets.php`;
            }
        }

    }
}
?>
