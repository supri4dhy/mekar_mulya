<?php
session_start();

function login($username, $password) {
    $users = json_decode(file_get_contents(__DIR__ . '/database/users.json'), true);
    foreach ($users as $user) {
        if ($user['username'] === $username && $user['password'] === $password) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['name'] = $user['name'];
            return true;
        }
    }
    return false;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function checkAuth() {
    if (!isLoggedIn()) {
        header('Location: index.php');
        exit();
    }
}

function logout() {
    session_destroy();
    header('Location: index.php');
    exit();
}
?>
