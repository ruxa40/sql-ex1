<?php
// Включаем вывод ошибок для отладки
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Подключаем файл для подключения к базе данных
require_once 'config/database.php';

// Инициализируем переменные для сообщений
$error_message = "";
$success_message = "";
$login_successful = false; // Флаг успешного входа

// Стартуем сессию
session_start();

// Проверяем метод запроса
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Проверяем, что оба поля заполнены
    if (empty($username) || empty($password)) {
        $error_message = "Все поля обязательны для заполнения!";
    } else {
        // SQL-запрос для проверки логина и пароля
        $sql = "SELECT ID_User, password, ban, ban_reason FROM users WHERE Username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->bind_result($user_id, $hashed_password, $ban, $ban_reason);

        if ($stmt->fetch()) {
            // Проверяем, заблокирован ли пользователь
            if ($ban) {
                $error_message = "Ваш аккаунт заблокирован. Причина: $ban_reason";
            } elseif (password_verify($password, $hashed_password)) {
                // Успешный вход
                $login_successful = true;
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $username;
            } else {
                $error_message = "Неверный логин или пароль!";
            }
        } else {
            $error_message = "Пользователь с таким логином не найден!";
        }

        $stmt->close();
    }
    $conn->close();

    // Перенаправляем на users-profile.php, если вход успешен
    if ($login_successful) {
        header("Location: users-profile.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Вход</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@48,400,0,0" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        @import url('https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@48,400,0,0');
        
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            padding: 20px;
        }

        .container {
            background: rgba(255, 255, 255, 0.9);
            padding: 35px;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            backdrop-filter: blur(10px);
        }

        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .form-header h1 {
            color: #2c3e50;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group input {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
        }

        .form-group label {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            color: #95a5a6;
            font-size: 15px;
            pointer-events: none;
            transition: all 0.3s ease;
            padding: 0 5px;
        }

        .form-group input:focus,
        .form-group input:not(:placeholder-shown) {
            border-color: #3498db;
            outline: none;
        }

        .form-group input:focus + label,
        .form-group input:not(:placeholder-shown) + label {
            top: 0;
            left: 12px;
            font-size: 12px;
            padding: 0 5px;
            color: #3498db;
            background: white;
        }

        .form-group input:focus {
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }

        .form-group input::placeholder {
            color: transparent;
        }

        .login-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 15px 0;
            font-size: 14px;
            color: #7f8c8d;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .remember-me input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: #3498db;
        }

        .forgot-password {
            color: #3498db;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .forgot-password:hover {
            color: #2980b9;
        }

        button {
            width: 100%;
            padding: 15px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        button:hover {
            background: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }

        .register-link {
            text-align: center;
            margin-top: 20px;
            color: #7f8c8d;
            font-size: 14px;
        }

        .register-link a {
            color: #3498db;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .register-link a:hover {
            color: #2980b9;
        }

        .notification {
            position: fixed;
            top: 25px;
            right: 30px;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px 20px;
            border-radius: 12px;
            color: white;
            font-size: 14px;
            opacity: 0;
            visibility: hidden;
            transform: translateX(30px);
            transition: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.35);
            z-index: 1000;
            background: rgba(231, 76, 60, 0.95);
            backdrop-filter: blur(6px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .notification::before {
            content: "error";
            font-family: 'Material Symbols Rounded';
            font-size: 24px;
            opacity: 0;
            animation: showIcon 0.1s ease forwards 0.2s;
        }

        @keyframes showIcon {
            to {
                opacity: 1;
            }
        }

        .notification-show {
            opacity: 1;
            visibility: visible;
            transform: translateX(0);
        }

        @media (max-width: 480px) {
            .container {
                padding: 20px;
            }

            .form-header h1 {
                font-size: 24px;
            }

            .form-group input {
                padding: 12px;
            }

            button {
                padding: 12px;
            }

            .login-options {
                flex-direction: column;
                gap: 10px;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <?php if (!empty($error_message)): ?>
        <div class="notification notification-show" id="notification">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <div class="container">
        <div class="form-header">
            <h1>Вход</h1>
        </div>
        <form method="post">
            <div class="form-group">
                <input type="text" id="username" name="username" required placeholder=" ">
                <label for="username">Логин</label>
            </div>
            <div class="form-group">
                <input type="password" id="password" name="password" required placeholder=" ">
                <label for="password">Пароль</label>
            </div>
            <div class="login-options">
                <div class="remember-me">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Запомнить меня</label>
                </div>
                <a href="#" class="forgot-password">Забыли пароль?</a>
            </div>
            <button type="submit">Войти</button>
            <div class="register-link">
                Нет аккаунта? <a href="registrka.php">Зарегистрироваться</a>
            </div>
        </form>
    </div>

    <script>
        const notification = document.getElementById('notification');
        if (notification) {
            setTimeout(() => {
                notification.style.transform = 'translateX(30px)';
                notification.style.opacity = '0';
                setTimeout(() => {
                    notification.classList.remove('notification-show');
                }, 500);
            }, 3000);
        }
    </script>
</body>
</html>
