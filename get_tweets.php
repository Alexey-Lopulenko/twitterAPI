<?php
// include config and twitter api wrappe
require_once('config.php');
require_once('setting_db.php');
require_once('TwitterAPIExchange.php');
require_once('MyClass.php');

$idPlayer = 1;



error_reporting(E_ALL);
ini_set('display_errors',true);

$data = $pdo->prepare("
SELECT players.name , teams.team , teams.university , players.high_school
FROM players
LEFT JOIN teams 
ON players.institution_id = teams.institution_id 
AND players.team_id = teams.p_team_id
WHERE  players.id = :id
");



$data->execute(['id' => $idPlayer]);
$row = $data->fetchAll();
//var_dump($row[0]);
//die;
echo '<h2>Data in db</h2>';
echo '<strong>name: </strong>'.$row[0]['name'].'<br>';
echo '<strong>team: </strong>'.$row[0]['team'].'<br>';
echo '<strong>high_school: </strong>'.$row[0]['high_school'].'<br>';
echo '<strong>university: </strong>'.$row[0]['university'].'<br>';

//die;
// settings for twitter api connection
$settings = array(
    'oauth_access_token' => TWITTER_ACCESS_TOKEN,
    'oauth_access_token_secret' => TWITTER_ACCESS_TOKEN_SECRET,
    'consumer_key' => TWITTER_CONSUMER_KEY,
    'consumer_secret' => TWITTER_CONSUMER_SECRET
);
?>
<h1>Twitter</h1>
<?php
$ttrE = new MyClass($settings);

echo '<hr><h2>test</h2>';
$test = $ttrE->getTweetInfo('1225164230766350341,78');
var_dump($test);die;


$searchList = $ttrE ->searchUser($row[0]['name']);
$paramFromFilter = [
        'name'=>$row[0]['name'],
        'university'=>$row[0]['university'],
        'abbreviate'=>$ttrE->abbreviate($row[0]['university']),
        'high_school'=>$row[0]['high_school'],
        'team'=>$row[0]['team'],
        'id'=>$idPlayer
];


$arrSuitableUsersByDescription = $ttrE->filterUsersByDescription($searchList, $paramFromFilter);
var_dump($arrSuitableUsersByDescription);

foreach ($arrSuitableUsersByDescription as $user){

   $arrTweets = $ttrE ->getUserTweets($user['screen_name']);
   $tweetStatistics = $ttrE->getTweetStatistics($arrTweets);
   echo '<hr>';
    var_dump($tweetStatistics);
   echo '<hr>';
}

die;

echo '<hr>';
//var_dump($searchList);

//filter users by tweet data
foreach ($arrSuitableUsersByDescription as $suitableUserData){
    foreach ($searchList as $userData){

        if($suitableUserData['screen_name'] != $userData['screen_name']){
            $arrTweets = $ttrE->getUserTweets($userData['screen_name']);
            if($ttrE->filterUsersByTweetData($arrTweets, $paramFromFilter)){
                echo "user".$userData['screen_name']."<br>";die('end');
            }
        }

    }
}
die;
if ($arrSuitableUserByDescription[0]['screen_name'] != ''):

  $tweets = $ttrE->getUserTweets($arrSuitableUser[0]['screen_name']);
var_dump($tweets);die;
    echo '<h1>user post</h1>';
    ?>

    <?php
//        echo '<strong>'.count($tweets).'</strong>';die;
//        var_dump($tweets);die;
    ?>


    <?php foreach ($tweets as $tweet) : ?>
    <img src="<?php echo $tweet['user']['profile_image_url']; ?>"/>
    <a href="https://twitter.com/<?php echo $tweet['user']['screen_name']; ?>" target="_blank">
        <b>@<?php echo $tweet['user']['screen_name']; ?></b>
    </a> tweeted:
    <br/>
    <br/>
    <?php echo $tweet['text']; ?>
    <br/>
    <br/>
    Tweeted on <?php echo $tweet['created_at']; ?>
    <br/>
    <br/>
    Like <?php echo $tweet['favorite_count']?>
    <br/>
    <br/>
    Retweet <?php echo $tweet['retweet_count'];?>
    <hr/>
<?php
//    echo '<pre>';
//    print_r($tweet['favorite_count']);
//    echo '</pre>';
//    ?>
<?php endforeach; ?>
<?php endif; ?>

<?php
$sql = "
INSERT INTO
players_twitter_data (player_id, twitter_url, count_tweets, count_followers, profile_image, retweets_tweet,likes_tweet, recent_post)
VALUES (?,?,?,?,?,?,?,?)";
//        $pdo->prepare($sql)->execute([$idPlayer, $url_profile, $item['statuses_count'], $item['followers_count'], $profileImg, $path_img_in_my_server, $today[0]]);
?>