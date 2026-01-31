<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['validated_picture'])) {
        $_SESSION['validated_picture'] = $_POST['validated_picture'];
        $_SESSION['picture_feedback'] = $_POST['picture_feedback'] ?? '';
    }
}

// Redirect back to form
header('Location: index.php');
exit;