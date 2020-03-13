<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2/12/20
 * Time: 6:07 PM
 */

require_once('setting_db.php');

class MyClass extends TwitterAPIExchange
{
    /** @var Response details about the result of the last request */
    private $response;
    const API_HOST = 'https://api.twitter.com';


    /**
     * @param $str
     * @param $startTag
     * @param $endTag
     * @param string $clearTag
     * @return bool|string
     */
   public function substrBetween($str, $startTag, $endTag, $clearTag = '')
    {
        if (strlen($clearTag)) $str = str_replace($clearTag, "", $str);
        $res = '';
        if (!empty($startTag))
            $i1 = stripos($str, $startTag);
        else $i1 = 0;
        if (!empty($endTag))
            $i2 = stripos($str, $endTag, $i1);
        else
            $i2 = strlen($str);
        if ($i1 !== false && $i2 !== false && $i1 < $i2) {
            $res = substr($str, $i1 + strlen($startTag), $i2 - $i1 - strlen($startTag));
        }
        return $res;
    }

    /**
     * @param $string
     * @return string
     */
    public function abbreviate($string)
    {

        $string = trim($string);

        if(strpos($string,',')) $string=$this->substrBetween($string,'',',');

        $abbreviation = "";

        $words = explode(" ", "$string");
        foreach ($words as $word) {


            if($word[0]=='(' || ctype_lower($word[0])===true) $word='';

            $word = ucwords($word);

            if($word != "") {
                $abbreviation .= $word[0];
            }
        }

        return $abbreviation;
    }


    /**
     * @param $username
     * @return array
     * @throws Exception
     * return all Search List by username
     */
    public function searchUser($username)
    {
        $varStop = '';
        $url = 'https://api.twitter.com/1.1/users/search.json';
        $numberPageSearch = 1;
        $getField = '?&page=' . $numberPageSearch . '&q=' . $username; //'?&page=2&count=20&q='

        // twitter api endpoint request type
        $requestMethod = 'GET';
        $followers = $this->setGetfield($getField)
            ->buildOauth($url, $requestMethod)
            ->performRequest();

        $json = json_decode($followers, true);
        $searchList = [];
        foreach ($json as $user) {
            array_push($searchList, $user);
        }

        if ($searchList != NULL) {
            while (!array_key_exists('errors', $json)) {
                $numberPageSearch++;
                $getField = '?&page=' . $numberPageSearch . '&q=' . $username; //'?&page=2&count=20&q='
                $followers = $this->setGetfield($getField)
                    ->buildOauth($url, $requestMethod)
                    ->performRequest();

                $json = json_decode($followers, true);

                foreach ($json as $user) {
                    foreach ($searchList as $userObj) {
                        if(array_key_exists('screen_name', $userObj)&&array_key_exists('screen_name', $user)&&($userObj['screen_name'] == $user['screen_name'])) {
                            $varStop = 'stop';
                        }
                    }
                    if ($varStop == 'stop') {
                        break;
                    }
                    array_push($searchList, $user);
                }
                if ($varStop == 'stop') {
                    break;
                }
            }
//            echo 'count pages search: ' . ($numberPageSearch - 1) . '<hr>';
//            echo 'count users search: ' . count($searchList) . '<hr>';
        }

        return $searchList;
    }


    public function filterUsersByDescription($searchList, $paramFromFilter)
    {
        $arrSuitableUser = [];
        foreach ($searchList as $item) {
            //filter users by data in description
            foreach ($paramFromFilter as $valueParam){
                if($valueParam !== ''){
                    if (array_key_exists('description', $item) && preg_match('%(^|\s+)' . $valueParam . '(\s+|,|\.)%i',$item['description'] )){
                        $profileImg = str_replace("normal", "400x400", $item['profile_image_url_https']);
                        $url_profile = "https://twitter.com/" . $item['screen_name'];

                        $duplicate = 'no';

                        foreach ($arrSuitableUser as $value){
                            if (in_array($item['screen_name'], $value, true)) {
                                $duplicate = 'yes';
                            }
                        }

                        if ($duplicate == 'no'){
                            $arrSuitableUser[] = [
                                'user_id'=> $item['id'],
                                'screen_name' => $item['screen_name'],
                                'url' => $url_profile,
                                'count_tweets' => $item['statuses_count'],
                                'count_followers' => $item['followers_count'],
                                'img' => $profileImg
                            ];
                        }
                    }
                }
            }
        }

        return $arrSuitableUser;
    }


    /**
     * @param $screen_name
     * @return mixed
     * @throws Exception
     */
    public function getUserTweets($screen_name)
    {
        // twitter api endpoint
        $url = 'https://api.twitter.com/1.1/statuses/user_timeline.json';

        $requestMethod = 'GET';

        // twitter api endpoint data
        $getfield = '?screen_name=' . $screen_name . '&count=200';

        // make our api call to twitter

        $this->setGetfield($getfield);
        $this->buildOauth($url, $requestMethod);
        $response = $this->performRequest(true, array(CURLOPT_SSL_VERIFYHOST => 0, CURLOPT_SSL_VERIFYPEER => 0));
        $tweets = json_decode($response, true);

        return $tweets;

    }

    /**
     * @param array $arrTweets
     * @param array $paramFromFilter
     * @return bool
     */
    public function filterUsersByTweetData( $arrTweets,  $paramFromFilter)
    {
        foreach ($arrTweets as $tweet) {
            foreach ($paramFromFilter as $valueParam) {
                if($valueParam !== ''){
                    if (is_array($tweet) && array_key_exists('text', $tweet) && preg_match('%(^|\s+)' . $valueParam . '(\s+|,|\.)%i', $tweet['text'])) {
                        return true;
                    }
                }
            }
        }
    }

    /**
     * @param array $arrTweets
     * @return array
     */
    public function getTweetStatistics(array $arrTweets)
    {
        $like_tweet = 0;
        $retweets_tweet = 0;
        $countTweet = 0;
        $recentTweetId = 0;

        if(!empty($arrTweets)){
            foreach ($arrTweets as $oneTweet){

                if(is_array($oneTweet)){
                    if(!array_key_exists('retweeted_status',$oneTweet) && array_key_exists('id',$oneTweet)){
                        if($recentTweetId == 0){
                            $recentTweetId = $oneTweet['id'];
                        }
                        $like_tweet += $oneTweet['favorite_count'];
                        $retweets_tweet += $oneTweet['retweet_count'];
                        $countTweet++;
                    }
                }
            }
            if($countTweet != 0 && $like_tweet != 0){
                $like_tweet = round(($like_tweet / $countTweet),2);
            }
            if($countTweet != 0 && $retweets_tweet != 0){
                $retweets_tweet = round(($retweets_tweet / $countTweet),2);
            }
            $arrResult = [
                'recentTweetId' => $recentTweetId,
                'like_tweet' => $like_tweet,
                'retweets_tweet' => $retweets_tweet
            ];

            return $arrResult;
        }
    }

    /**
     * @param $screen_name (The screen name of the user for whom to return results. Either a id or screen_name is required for this method.)
     * @return array
     * @throws Exception
     */
    public function getUserInfo($screen_name)
    {

        // twitter api endpoint
        $url = 'https://api.twitter.com/1.1/users/show.json';

        $requestMethod = 'GET';

        // twitter api endpoint data
        $request = '?screen_name='.$screen_name;

        // make our api call to twitter

        $this->setGetfield($request);
        $this->buildOauth($url, $requestMethod);
        $response = $this->performRequest(true, array(CURLOPT_SSL_VERIFYHOST => 0, CURLOPT_SSL_VERIFYPEER => 0));
        $groupInfo = json_decode($response, true);

        $arrGroupTweets = $this->getUserTweets($screen_name);

        $tweetStat = $this->getTweetStatistics($arrGroupTweets);

//        var_dump($tweetStat);die;

        $arrGroupInfo = [
            'count_tweet' => $groupInfo['statuses_count'],
            'count_followers' => $groupInfo['followers_count'],
            'profile_image' => $this->getImg($groupInfo['profile_image_url_https']),
            'retweets_tweet' => $tweetStat['retweets_tweet'],
            'likes_tweet' => $tweetStat['like_tweet'],
            'recent_post' => 'https://twitter.com/JalenLovett/status/'.$tweetStat['recentTweetId'],
        ];

//        var_dump( $arrGroupInfo);die;

        return $arrGroupInfo;
    }


    /**
     * @param $image_url_https
     * @return mixed (image 400x400)
     */
    public function getImg($image_url_https)
    {
        $profileImg = str_replace("normal", "400x400", $image_url_https);

        return $profileImg;
    }



    public function sendReportToTelegram($message)
    {
        // сюда нужно вписать токен вашего бота
        define('TELEGRAM_TOKEN', '1009131468:AAHuUUaCmHEBXjK8etHh6JjG0PQMrh3RTZE');

// сюда нужно вписать ваш внутренний айдишник
        define('TELEGRAM_CHATID', '554905211');

            $ch = curl_init();
            curl_setopt_array(
                $ch,
                array(
                    CURLOPT_URL => 'https://api.telegram.org/bot' . TELEGRAM_TOKEN . '/sendMessage',
                    CURLOPT_POST => TRUE,
                    CURLOPT_RETURNTRANSFER => TRUE,
                    CURLOPT_TIMEOUT => 10,
                    CURLOPT_POSTFIELDS => array(
                        'chat_id' => TELEGRAM_CHATID,
                        'text' => $message,
                    ),
                )
            );
            curl_exec($ch);
    }


    public function getLimits()
    {
        $url = 'https://api.twitter.com/1.1/application/rate_limit_status.json';

        $requestMethod = 'GET';

        // twitter api endpoint data
        $getfield = '?resources=help,users,search,statuses';

        // make our api call to twitter

        $this->setGetfield($getfield);
        $this->buildOauth($url, $requestMethod);
        $response = $this->performRequest(true, array(CURLOPT_SSL_VERIFYHOST => 0, CURLOPT_SSL_VERIFYPEER => 0));
        $limits = json_decode($response, true);

        return $limits;
    }
}