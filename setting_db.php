
<?php
$dsn = 'mysql:dbname=ncaa;host=localhost;port=8889';
$user = 'root';
$password = 'root';
try {
    GLOBAL $pdo;
    $pdo = new PDO($dsn, $user, $password);

} catch (PDOException $e) {
    echo 'Подключение не удалось: ' . $e->getMessage();
}