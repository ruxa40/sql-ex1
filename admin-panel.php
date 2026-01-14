<?php
session_start();
include_once 'config/database.php';

// Обработка выхода из системы
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: loginka.php");
    exit();
}

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header("Location: loginka.php");
    exit();
}

// Проверка роли
$user_id = $_SESSION['user_id'];
$result = $conn->query("SELECT ID_Role FROM users WHERE ID_User = $user_id");
$row = $result->fetch_assoc();

if ($row['ID_Role'] != 1) { // 1 - Администратор
    die("У вас нет доступа к этой странице.");
}

// Обработка блокировки пользователя
if (isset($_POST['ban_user'])) {
    $ban_id = $_POST['ban_user_id'];
    $ban_reason = $conn->real_escape_string($_POST['ban_reason']);
    $conn->query("UPDATE users SET ban = 1, ban_reason = '$ban_reason' WHERE ID_User = $ban_id");
    echo "Пользователь заблокирован.";
}

// Обработка разблокировки пользователя
if (isset($_POST['unban_user'])) {
    $unban_id = $_POST['unban_user_id'];
    $conn->query("UPDATE users SET ban = 0, ban_reason = NULL WHERE ID_User = $unban_id");
    echo "Пользователь разблокирован.";
}

// Получение списка пользователей
$users = $conn->query("SELECT * FROM users");
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ панель</title>
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

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }

        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 60px;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            padding: 0 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: #2c3e50;
            font-weight: 600;
            font-size: 1.5rem;
        }

        .logo img {
            height: 35px;
        }

        .nav-profile {
            position: relative;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .nav-profile:hover {
            background: rgba(52, 152, 219, 0.1);
        }

        .username {
            color: #2c3e50;
            font-weight: 500;
        }

        .nav-profile::after {
            content: 'expand_more';
            font-family: 'Material Symbols Rounded';
            font-size: 20px;
            color: #2c3e50;
            transition: transform 0.3s ease;
        }

        .nav-profile.active::after {
            transform: rotate(180deg);
        }

        .profile-dropdown {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            padding: 0.5rem 0;
            min-width: 200px;
            display: none;
            animation: dropdownFade 0.2s ease;
        }

        @keyframes dropdownFade {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .profile-dropdown.show {
            display: block;
        }

        .profile-dropdown a {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            color: #2c3e50;
            text-decoration: none;
            transition: all 0.3s ease;
            gap: 12px;
        }

        .profile-dropdown a:hover {
            background: rgba(52, 152, 219, 0.1);
        }

        .sidebar {
            position: fixed;
            left: 0;
            top: 60px;
            bottom: 0;
            width: 250px;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            padding: 0;
            box-shadow: 2px 0 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            z-index: 999;
        }

        .sidebar-nav {
            list-style: none;
            padding: 1.5rem;
        }

        .nav-item a {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: #2c3e50;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s;
            gap: 10px;
            margin-bottom: 0.5rem;
        }

        .nav-item a:hover,
        .nav-item a.active {
            background: #3498db;
            color: white;
        }

        .nav-heading {
            font-size: 0.75rem;
            text-transform: uppercase;
            color: #95a5a6;
            margin: 2rem 0 1rem;
            padding-left: 1rem;
        }

        .main {
            margin-left: 250px;
            margin-top: 60px;
            padding: 2rem;
            transition: margin-left 0.3s ease;
        }

        .admin-container {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            backdrop-filter: blur(10px);
        }

        .admin-header {
            margin-bottom: 2rem;
        }

        .admin-header h1 {
            color: #2c3e50;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            background: white;
            border-radius: 12px;
            overflow: hidden;
        }

        .users-table th,
        .users-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
        }

        .users-table th {
            background: #f8f9fa;
            color: #2c3e50;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }

        .users-table tr:last-child td {
            border-bottom: none;
        }

        .users-table tr:hover {
            background: #f8f9fa;
        }

        .status-active {
            color: #27ae60;
            font-weight: 500;
        }

        .status-banned {
            color: #e74c3c;
            font-weight: 500;
        }

        .button {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            gap: 6px;
            font-size: 0.875rem;
        }

        .button-danger {
            background: #e74c3c;
            color: white;
        }

        .button-success {
            background: #2ecc71;
            color: white;
        }

        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
        }

        .ban-form {
            display: flex;
            gap: 0.5rem;
        }

        .ban-input {
            padding: 0.5rem;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            font-size: 0.875rem;
        }

        .toggle-sidebar-btn {
            background: none;
            border: none;
            color: #2c3e50;
            cursor: pointer;
            padding: 0;
            display: none;
            width: 40px;
            height: 40px;
            position: relative;
            border-radius: 8px;
            transition: all 0.3s ease;
            z-index: 1000;
            margin-right: 10px;
        }

        .toggle-sidebar-btn .material-symbols-rounded {
            font-size: 24px;
            transition: all 0.3s cubic-bezier(0.4, 0.0, 0.2, 1);
            transform-origin: center;
        }

        @media (max-width: 992px) {
            .toggle-sidebar-btn {
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main {
                margin-left: 0;
                padding: 1rem;
            }

            .admin-container {
                padding: 1.5rem;
            }

            .users-table {
                display: block;
                overflow-x: auto;
            }
        }

        @media (max-width: 480px) {
            .header {
                padding: 0 0.5rem;
            }

            .admin-container {
                padding: 1rem;
            }

            .admin-header h1 {
                font-size: 1.5rem;
            }

            .ban-form {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div style="display: flex; align-items: center;">
            <button class="toggle-sidebar-btn">
                <span class="material-symbols-rounded">menu</span>
            </button>
            <a href="index.php" class="logo">
                <img src="assets/img/logo.svg" alt="">
                <span>SQL</span>
            </a>
        </div>
        <div class="nav-profile">
            <span class="username"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Гость'); ?></span>
            <div class="profile-dropdown">
                <a href="index.php">
                    <span class="material-symbols-rounded">home</span>
                    Главная
                </a>
                <a href="users-profile.php">
                    <span class="material-symbols-rounded">person</span>
                    Мой профиль
                </a>
                <a href="?logout=true">
                    <span class="material-symbols-rounded">logout</span>
                    Выйти
                </a>
            </div>
        </div>
    </header>

    <aside class="sidebar">
        <ul class="sidebar-nav">
            <li class="nav-item">
                <a href="index.php">
                    <span class="material-symbols-rounded">grid_view</span>
                    Задания
                </a>
            </li>
            <li class="nav-item">
                <a href="admin-panel.php" class="active">
                    <span class="material-symbols-rounded">admin_panel_settings</span>
                    Админ панель
                </a>
            </li>
            <li class="nav-item">
                <a href="users-profile.php">
                    <span class="material-symbols-rounded">person</span>
                    Профиль
                </a>
            </li>
            <li class="nav-heading">ПОЛЕЗНЫЕ ССЫЛКИ</li>
            <li class="nav-item">
                <a href="pages-faq.html">
                    <span class="material-symbols-rounded">help</span>
                    О нас
                </a>
            </li>
            <li class="nav-item">
                <a href="pages-contact.html">
                    <span class="material-symbols-rounded">mail</span>
                    Контакты
                </a>
            </li>
        </ul>
    </aside>

    <main class="main">
        <div class="admin-container">
            <div class="admin-header">
                <h1>Управление пользователями</h1>
            </div>
            <div class="table-responsive">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Имя пользователя</th>
                            <th>Email</th>
                            <th>Роль</th>
                            <th>Whitelist</th>
                            <th>Статус</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = $users->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['ID_User']) ?></td>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= $user['ID_Role'] == 1 ? 'Администратор' : 'Пользователь' ?></td>
                            <td><?= $user['whitelist'] ? 'Да' : 'Нет' ?></td>
                            <td class="<?= $user['ban'] ? 'status-banned' : 'status-active' ?>">
                                <?= $user['ban'] ? 'Заблокирован' : 'Активен' ?>
                            </td>
                            <td>
                                <?php if (!$user['ban'] && !$user['whitelist']): ?>
                                <form method="post" class="ban-form">
                                    <input type="hidden" name="ban_user_id" value="<?= htmlspecialchars($user['ID_User']) ?>">
                                    <input type="text" name="ban_reason" placeholder="Причина блокировки" required class="ban-input">
                                    <button type="submit" name="ban_user" class="button button-danger">
                                        <span class="material-symbols-rounded">block</span>
                                        Заблокировать
                                    </button>
                                </form>
                                <?php elseif ($user['ban']): ?>
                                <form method="post" class="ban-form">
                                    <input type="hidden" name="unban_user_id" value="<?= htmlspecialchars($user['ID_User']) ?>">
                                    <button type="submit" name="unban_user" class="button button-success">
                                        <span class="material-symbols-rounded">check_circle</span>
                                        Разблокировать
                                    </button>
                                </form>
                                <?php else: ?>
                                <span class="text-muted">Нельзя заблокировать</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        const toggleBtn = document.querySelector('.toggle-sidebar-btn');
        const sidebar = document.querySelector('.sidebar');
        const main = document.querySelector('.main');

        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('show');
            main.classList.toggle('shifted');
            toggleBtn.classList.toggle('active');
            
            const icon = toggleBtn.querySelector('.material-symbols-rounded');
            if (sidebar.classList.contains('show')) {
                icon.textContent = 'close';
            } else {
                icon.textContent = 'menu';
            }
        });

        const profileBtn = document.querySelector('.nav-profile');
        const dropdown = document.querySelector('.profile-dropdown');

        profileBtn.addEventListener('click', () => {
            dropdown.classList.toggle('show');
            profileBtn.classList.toggle('active');
        });

        document.addEventListener('click', (e) => {
            if (!profileBtn.contains(e.target)) {
                dropdown.classList.remove('show');
                profileBtn.classList.remove('active');
            }
            
            if (window.innerWidth <= 992) {
                if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target) && sidebar.classList.contains('show')) {
                    sidebar.classList.remove('show');
                    main.classList.remove('shifted');
                    toggleBtn.classList.remove('active');
                    const icon = toggleBtn.querySelector('.material-symbols-rounded');
                    icon.textContent = 'menu';
                }
            }
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth > 992) {
                sidebar.classList.remove('show');
                main.classList.remove('shifted');
                toggleBtn.classList.remove('active');
                const icon = toggleBtn.querySelector('.material-symbols-rounded');
                icon.textContent = 'menu';
            }
        });
    </script>
</body>
</html>
