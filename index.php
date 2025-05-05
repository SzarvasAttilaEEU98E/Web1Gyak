<?php
session_start();
include('config.php');

// Alapértelmezett menüpont
$default_page = 'home';
$page = isset($_GET['page']) ? $_GET['page'] : $default_page;

// Ellenőrizzük, hogy létezik-e a kért menüpont
if (!array_key_exists($page, $config['menu'])) {
    $page = $default_page;
}

// Képfeltöltés kezelése
$gallery_images = [];
$max_file_size = 500 * 1024; // 500 KB
$allowed_types = ['image/jpeg', 'image/png'];
$upload_dir = 'uploads/';

if ($page === 'upload' && isset($_POST['upload']) && isset($_SESSION['user'])) {
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $file = $_FILES['image'];
    if ($file['error'] === UPLOAD_ERR_OK) {
        // Kisbetűssé alakítjuk a fájltípust az összehasonlításhoz
        $file_type = strtolower($file['type']);
        if ($file['size'] <= $max_file_size && in_array($file_type, $allowed_types)) {
            $filename = $upload_dir . basename($file['name']);
            if (!file_exists($filename)) {
                move_uploaded_file($file['tmp_name'], $filename);
                $success = "Kép sikeresen feltöltve!";
                $uploaded_image = $filename;
            } else {
                $error = "A fájl már létezik!";
            }
        } else {
            $error = "Érvénytelen fájl méret vagy típus! (Fájltípus: " . $file['type'] . ")";
        }
    } else {
        $error = "Hiba a fájl feltöltése során! (Hibakód: " . $file['error'] . ")";
    }
}

// Galéria képek betöltése
if ($page === 'gallery' && isset($_SESSION['user'])) {
    if (is_dir($upload_dir)) {
        $gallery_images = glob($upload_dir . '*.{jpg,jpeg,png}', GLOB_BRACE);
    }
}

// Kapcsolat űrlap kezelése
if ($page === 'contact' && isset($_POST['contact'])) {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $message = $_POST['message'] ?? '';

    if (empty($name) || empty($email) || empty($message)) {
        $error = "Minden mezőt ki kell tölteni!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Érvénytelen email cím!";
    } elseif (strlen($message) < 10) {
        $error = "Az üzenet túl rövid, minimum 10 karakter szükséges!";
    } else {
        $messages = file_exists('messages.json') ? json_decode(file_get_contents('messages.json'), true) : [];
        $sender_name = isset($_SESSION['user']) ? $_SESSION['user']['family_name'] . ' ' . $_SESSION['user']['given_name'] : 'Vendég';
        $messages[] = [
            'name' => $sender_name,
            'email' => $email,
            'message' => $message,
            'created_at' => date('Y-m-d H:i:s')
        ];
        file_put_contents('messages.json', json_encode($messages));
        $success = "Üzenet elküldve!";
    }
}

// Üzenetek betöltése
if ($page === 'messages' && isset($_SESSION['user'])) {
    $messages = file_exists('messages.json') ? json_decode(file_get_contents('messages.json'), true) : [];
}

// Bejelentkezés kezelése
if ($page === 'login' && isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $users = file_exists('users.json') ? json_decode(file_get_contents('users.json'), true) : [];
    foreach ($users as $user) {
        if ($user['username'] === $username && password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user;
            header('Location: index.php?page=home');
            exit;
        }
    }
    $error = "Hibás felhasználónév vagy jelszó!";
}

// Regisztráció kezelése
if ($page === 'register' && isset($_POST['register'])) {
    $family_name = $_POST['family_name'] ?? '';
    $given_name = $_POST['given_name'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($family_name) || empty($given_name) || empty($username) || empty($password)) {
        $error = "Minden mezőt ki kell tölteni!";
    } elseif (strlen($username) < 3) {
        $error = "A felhasználónév túl rövid, minimum 3 karakter szükséges!";
    } elseif (strlen($password) < 6) {
        $error = "A jelszó túl rövid, minimum 6 karakter szükséges!";
    } else {
        $users = file_exists('users.json') ? json_decode(file_get_contents('users.json'), true) : [];
        foreach ($users as $user) {
            if ($user['username'] === $username) {
                $error = "A felhasználónév már foglalt!";
                include 'main.html';
                exit;
            }
        }
        $users[] = [
            'family_name' => $family_name,
            'given_name' => $given_name,
            'username' => $username,
            'password' => password_hash($password, PASSWORD_DEFAULT)
        ];
        file_put_contents('users.json', json_encode($users));
        $success = "Sikeres regisztráció! Kérlek, jelentkezz be!";
    }
}

// Kijelentkezés kezelése
if ($page === 'logout') {
    session_destroy();
    header('Location: index.php?page=home');
    exit;
}

// HTML fájl betöltése
include 'main.html';