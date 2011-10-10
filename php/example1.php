<?php
require 'PasswordServer.php';
$pws = new PasswordServer(8099, 'localhost');
echo $pws->generatePassword(array('RandomBits' => 30)) . "\n";
?>
