<?php
session_start();
session_unset();
session_destroy();

header('Location: /specialization-tracker/login.php');
exit;
?>