<?php
require_once('config.php');
require_once('setting_db.php');
require_once('TwitterAPIExchange.php');
require_once('MyClass.php');
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

$contFoDate = "                           </tr>
                           
                        </tbody>
                        
                     </table>
                     
                  </div>
                  
               </div>
               
               <p>&nbsp;</p>
               
               <p>Feb. 27, 2020</p>
               
               <div class=\"thumbnail extra-small-with-caption\"><img class=\"img-responsive\" src=\"/manship/images/people/faculty-and-staff-headshots-2019/len-apcar.jpg\" alt=\"Len Apcar\" width=\"175\" height=\"263\">
                  
                  <p style=\"text-align: center;\">Leonard Apcar, Wendell Gray Switzer Jr. Endowed Chair in Media Literacy</p>
                  
               </div>";
preg_match_all('%(Jan.|Feb.|Mar.|Apr.|May.|Jun.|Jul.|Aug.|Sept.|Oct.|Nov.|Dec.) (0[1-9]|1[0-9]|2[0-9]|3[01]), [0-9]{4}%', $contFoDate, $textDd);


//preg_match_all('%(?<=\/(?!.*\/)\s)([\s\S]+?)(?=$)%', $contFoDate, $textDd);
var_dump($textDd[0][0]);
//$date=date('F d Y', strtotime($textDd[0][0]));
//var_dump($date);

//$textFoDate = str_replace('https://www.theknoxstudent.com/','',$contFoDate);
//$arrDate = explode('/', $textFoDate);
//$date = strtotime($arrDate[1] . $arrDate[2] . $arrDate[3]);
//$date = date('F d Y', $date);
//var_dump($date);
$institutions = $pdo->query("SELECT id FROM institutions WHERE (id >= {$startInstitution} and id <={$endInstitution}) ORDER BY id ASC")->fetchAll();
var_dump($institutions);