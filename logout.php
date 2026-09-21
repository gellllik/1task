<?php
require __DIR__ . '/functions.php';
$_SESSION = [];
session_destroy();
redirect('login.php');