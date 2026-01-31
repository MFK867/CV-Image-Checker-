<?php
session_start();
unset($_SESSION['validated_picture']);
unset($_SESSION['picture_feedback']);
header('Location: index.php');
exit;