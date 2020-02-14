
<?php
$dsn = 'mysql:dbname=ncaa;host=localhost';
$user = 'root';
$password = 'root';

try {

    $pdo = new PDO($dsn, $user, $password);
//    var_dump($pdo);
    /*    echo '<hr>';
        $data = $pdo->query("SELECT * FROM news")->fetchAll();
        foreach ($data as $row) {
           echo $row['h1']."<br />\n";
           echo $row['text']."<br />\n";
           echo $row['created']."<br />\n";
           echo $row['img']."<br />\n";
           echo '<hr>';
        }*/

} catch (PDOException $e) {
    echo 'Подключение не удалось: ' . $e->getMessage();
}