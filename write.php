<?php
$user = trim($_POST['user'] ?? '');
$msg  = trim($_POST['msg'] ?? '');
if(!$user || !$msg) exit;

$line = date('[H:i] ') . '<span class="user">' . htmlspecialchars($user) . '</span>: <span class="msg">' . htmlspecialchars($msg) . '</span>' . "\n";
file_put_contents('messages.txt',$line,FILE_APPEND);
?>
