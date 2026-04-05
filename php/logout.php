<?php
session_start();
$_SESSION = [];
session_destroy();
header('Location: /buea-bloodlink-frontend/index.html');
exit;
