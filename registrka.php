<?php
// Включаем вывод ошибок для отладки
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Подключаем файл для подключения к базе данных
require_once 'config/database.php';

// Инициализируем переменные для ошибок и сообщений
$error_message = "";
$success_message = "";

// Проверяем метод запроса
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из формы
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Проверяем, что все поля заполнены
    if (empty($username) || empty($email) || empty($password)) {
        $error_message = "Все поля обязательны для заполнения!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Проверяем корректность email
        $error_message = "Некорректный формат email!";
    } else {
        // Проверяем, есть ли пользователь с таким логином или почтой
        $checkUserSql = "SELECT COUNT(*) FROM users WHERE Username = ? OR email = ?";
        $checkStmt = $conn->prepare($checkUserSql);
        $checkStmt->bind_param("ss", $username, $email);
        $checkStmt->execute();
        $checkStmt->bind_result($userExists);
        $checkStmt->fetch();
        $checkStmt->close();

        if ($userExists > 0) {
            $error_message = "Пользователь с таким логином или почтой уже зарегистрирован!";
        } else {
            // Хэшируем пароль
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Устанавливаем стандартные значения
            $role = 0; // Пользователь
            $ban = 0;  // Не забанен
            $whitelist = 0; // Можно заблокировать

            // Проверяем, существует ли роль в таблице roles
            $checkRoleSql = "SELECT COUNT(*) FROM roles WHERE ID_Role = ?";
            $checkStmt = $conn->prepare($checkRoleSql);
            $checkStmt->bind_param("i", $role);
            $checkStmt->execute();
            $checkStmt->bind_result($roleExists);
            $checkStmt->fetch();
            $checkStmt->close();

            if ($roleExists == 0) {
                $error_message = "Ошибка: Роль с ID $role не существует.";
            } else {
                // SQL-запрос для добавления пользователя
                $sql = "INSERT INTO users (ID_Role, Username, email, password, ban, whitelist) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);

                if ($stmt) {
                    $stmt->bind_param("isssii", $role, $username, $email, $hashed_password, $ban, $whitelist);

                    if ($stmt->execute()) {
                        // Успешная регистрация
                        header("Location: index.php");
                        exit;
                    } else {
                        $error_message = "Ошибка выполнения запроса: " . $stmt->error;
                    }

                    $stmt->close();
                } else {
                    $error_message = "Ошибка подготовки запроса: " . $conn->error;
                }
            }
        }
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Регистрация</title>
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

        .login-link {
            text-align: center;
            margin-top: 20px;
            color: #7f8c8d;
            font-size: 14px;
        }

        .login-link a {
            color: #3498db;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .login-link a:hover {
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
            <h1>Регистрация</h1>
        </div>
        <form method="post">
            <div class="form-group">
                <input type="text" id="username" name="username" required placeholder=" ">
                <label for="username">Логин</label>
            </div>
            <div class="form-group">
                <input type="email" id="email" name="email" required placeholder=" ">
                <label for="email">Почта</label>
            </div>
            <div class="form-group">
                <input type="password" id="password" name="password" required placeholder=" ">
                <label for="password">Пароль</label>
            </div>
            <button type="submit">Зарегистрироваться</button>
            <div class="login-link">
                Есть аккаунт? <a href="loginka.php">Войти</a>
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
