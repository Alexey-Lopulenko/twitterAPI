<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2/12/20
 * Time: 6:07 PM
 */

class MyClass extends TwitterAPIExchange
{
    /** @var Response details about the result of the last request */
    private $response;
    const API_HOST = 'https://api.twitter.com';


    /**
     * @param $string
     * @return string
     */
    public function abbreviate($string)
    {

        $string = trim($string);

        if(strpos($string,',')) $string=substrBetween($string,'',',');

        $abbreviation = "";

        $words = explode(" ", "$string");
        foreach ($words as $word) {


            if($word[0]=='(' || ctype_lower($word[0])===true) $word='';

            $word = ucwords($word);

            $abbreviation .= $word[0];
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
            echo 'count pages search: ' . ($numberPageSearch - 1) . '<hr>';
            echo 'count users search: ' . count($searchList) . '<hr>';
        }

        return $searchList;
    }


    public function filterUsersByDescription($searchList, $paramFromFilter)
    {
        $arrSuitableUser = [];
        foreach ($searchList as $item) {
            //filter users by data in description
            foreach ($paramFromFilter as $valueParam){
                if (array_key_exists('description', $item) && preg_match('%(^|\s+)' . $valueParam . '(\s+|,|\.)%',$item['description'] )) {
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
                            'player_id' => $paramFromFilter['id'],
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
//        var_dump($paramFromFilter);

        foreach ($arrTweets as $tweet) {
//            var_dump($tweet);die;

            foreach ($paramFromFilter as $valueParam) {
//                var_dump($tweet);die;
                if (array_key_exists('text', (array)$tweet) && preg_match('%(^|\s+)' . $valueParam . '(\s+|,|\.)%', $tweet['text'])) {
                    return true;
                }
            }
        }

//          if(array_key_exists('text', $tweet) && stristr($tweet['text'], $paramFromFilter['university'])){
//            return true;
//          }
//          elseif (array_key_exists('text', $tweet) && stristr($tweet['text'], $paramFromFilter['abbreviate'])){
//              return true;
//          }
//          elseif (array_key_exists('text', $tweet) && stristr($tweet['text'], $paramFromFilter['high_school'])){
//              return true;
//          }
//          elseif (array_key_exists('text', $tweet) && stristr($tweet['text'], $paramFromFilter['team'])){
//              return true;
//          }else{
//              return false;
//          }

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

        foreach ($arrTweets as $oneTweet){

            if(!array_key_exists('retweeted_status',$oneTweet)){
                if($recentTweetId == 0){
                    $recentTweetId = $oneTweet['id'];
                }
                $like_tweet += $oneTweet['favorite_count'];
                $retweets_tweet += $oneTweet['retweet_count'];
                $countTweet++;
            }
        }
        $arrResult = [
            'recentTweetId' => $recentTweetId,
            'like_tweet' => round(($like_tweet / $countTweet),2),
            'retweets_tweet' => round(($retweets_tweet / $countTweet),2)
        ];

        return $arrResult;

    }
}