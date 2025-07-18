<?php
// Генерация нового пароля от админки. Вставить в базу данных, в backend_users.password
$password = 'uYVk6y4Kh8Y6uSymyRq';
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
echo $hash;
?>