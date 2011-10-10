<?php
require 'PasswordServer.php';
$pws = new PasswordServer(8099, 'localhost');
$result = $pws->checkPassword('foobar');
print_r ($result);
echo "\n";
?>
