<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: loginka.php");
    exit();
}

include("/xampp/htdocs/html/config/database.php");

$user_id = $_SESSION['user_id'];

// Запрос с JOIN для получения информации о пользователе
$query = "SELECT users.Username, users.email, roles.Name_role, users.avatar_path 
          FROM users 
          JOIN roles ON users.ID_Role = roles.ID_Role 
          WHERE users.ID_User = ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    die("Ошибка подготовки запроса: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username, $email, $role_name, $avatar_path);
$stmt->fetch();
$stmt->close();

// Установка пути к аватару по умолчанию, если он не задан
if (empty($avatar_path)) {
    $avatar_path = "uploads/avatars/default.jpg";
}

$user_role = null;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $role_query = "SELECT ID_Role FROM users WHERE ID_User = ?";
    $stmt = $conn->prepare($role_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $user_role = $row['ID_Role'];
    }
    $stmt->close();
}

// Обработка загрузки аватара
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['avatar'])) {
    $target_dir = "uploads/avatars/";
    $file_name = basename($_FILES["avatar"]["name"]);
    $target_file = $target_dir . uniqid() . "_" . $file_name;
    $upload_ok = true;
    $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Проверяем, является ли файл изображением
    $check = getimagesize($_FILES["avatar"]["tmp_name"]);
    if ($check === false) {
        echo "Файл не является изображением.";
        $upload_ok = false;
    }

    // Ограничение по типу файла
    if (!in_array($image_file_type, ["jpg", "jpeg", "png", "gif"])) {
        echo "Только JPG, JPEG, PNG, и GIF форматы поддерживаются.";
        $upload_ok = false;
    }

    // Проверка размера файла (до 2МБ)
    if ($_FILES["avatar"]["size"] > 2000000) {
        echo "Файл слишком большой.";
        $upload_ok = false;
    }

    // Если файл прошёл проверки, загружаем его
    if ($upload_ok) {
        if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $target_file)) {
            // Обновляем путь к аватару в базе данных
            $stmt = $conn->prepare("UPDATE users SET avatar_path = ? WHERE ID_User = ?");
            $stmt->bind_param("si", $target_file, $user_id);
            $stmt->execute();
            $stmt->close();

            // Обновляем текущий путь к аватару
            $avatar_path = $target_file;

            echo "Аватар успешно загружен.";
        } else {
            echo "Произошла ошибка при загрузке файла.";
        }
    }
}

// Обработка выхода
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: loginka.php");
    exit();
}

// Получаем расширенную статистику пользователя
$stats_query = "
    SELECT 
        (SELECT COUNT(*) FROM tasks) as total_tasks,
        COUNT(DISTINCT CASE WHEN ut.is_solved = 1 THEN ut.task_id END) as solved_tasks,
        COUNT(DISTINCT ut.task_id) as attempted_tasks,
        COUNT(DISTINCT CASE WHEN ut.is_solved = 1 THEN ut.task_id END) * 100.0 / NULLIF(COUNT(DISTINCT ut.task_id), 0) as success_rate
    FROM user_tasks ut 
    WHERE ut.user_id = ?
";
$stmt = $conn->prepare($stats_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stats_result = $stmt->get_result();
$stats = $stats_result->fetch_assoc();
$stmt->close();

$total_tasks = $stats['total_tasks'];
$solved_tasks = $stats['solved_tasks'];
$attempted_tasks = $stats['attempted_tasks'];
$success_rate = $stats['success_rate'] ? round($stats['success_rate']) : 0;
$completion_rate = $total_tasks > 0 ? round(($solved_tasks / $total_tasks) * 100) : 0;

?>


<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль</title>
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

        .profile-container {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            backdrop-filter: blur(10px);
        }

        .profile-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .profile-header h1 {
            color: #2c3e50;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .profile-img-container {
            position: relative;
            width: 150px;
            height: 150px;
            margin: 0 auto 1.5rem;
        }

        .profile-img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #fff;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .profile-img:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }

        .profile-img-container::after {
            content: 'photo_camera';
            font-family: 'Material Symbols Rounded';
            position: absolute;
            bottom: 0;
            right: 0;
            background: #3498db;
            color: white;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .profile-img-container:hover::after {
            transform: scale(1.1);
        }

        .profile-details {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-top: 2rem;
        }

        .profile-info {
            display: grid;
            gap: 1.5rem;
        }

        .info-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .info-label {
            color: #95a5a6;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-value {
            color: #2c3e50;
            font-size: 1.125rem;
            font-weight: 500;
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

            .profile-container {
                padding: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .header {
                padding: 0 0.5rem;
            }

            .profile-container {
                padding: 1rem;
            }

            .profile-header h1 {
                font-size: 1.5rem;
            }

            .profile-img-container {
                width: 120px;
                height: 120px;
            }
        }

        #avatar-input {
            display: none;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.5);
            border-radius: 12px;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.8);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: #3498db;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-icon .material-symbols-rounded {
            font-size: 24px;
            color: white;
        }

        .stat-info {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 600;
            color: #2c3e50;
        }

        .stat-label {
            font-size: 0.875rem;
            color: #7f8c8d;
        }

        .progress-bar {
            width: 100%;
            height: 8px;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 4px;
            overflow: hidden;
        }

        .progress {
            height: 100%;
            background: #3498db;
            border-radius: 4px;
            transition: width 0.3s ease;
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .stat-card {
                padding: 1rem;
            }

            .stat-icon {
                width: 40px;
                height: 40px;
            }

            .stat-icon .material-symbols-rounded {
                font-size: 20px;
            }

            .stat-value {
                font-size: 1.25rem;
            }
        }

        .stats-grid {
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
        }

        .recent-tasks-list {
            background: rgba(255, 255, 255, 0.5);
            border-radius: 12px;
            overflow: hidden;
            margin-top: 1rem;
        }

        .recent-task-item {
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .recent-task-item:last-child {
            border-bottom: none;
        }

        .recent-task-item:hover {
            background: rgba(255, 255, 255, 0.8);
        }

        .task-info {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .task-name {
            color: #2c3e50;
            font-weight: 600;
        }

        .task-description {
            color: #7f8c8d;
            font-size: 0.875rem;
        }

        .task-date {
            color: #95a5a6;
            font-size: 0.875rem;
        }

        @media (max-width: 992px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .recent-task-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .task-date {
                align-self: flex-end;
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
            <span class="username"><?php echo htmlspecialchars($username); ?></span>
            <div class="profile-dropdown">
                <a href="index.php">
                    <span class="material-symbols-rounded">home</span>
                    Главная
                </a>
                <?php if ($user_role == 1): ?>
                <a href="admin-panel.php">
                    <span class="material-symbols-rounded">admin_panel_settings</span>
                    Админ панель
                </a>
                <?php endif; ?>
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
            <?php if ($user_role == 1): ?>
            <li class="nav-item">
                <a href="admin-panel.php">
                    <span class="material-symbols-rounded">admin_panel_settings</span>
                    Админ панель
                </a>
            </li>
            <?php endif; ?>
            <li class="nav-item" >
                <a href="users-profile.php" class="active">
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
        <div class="profile-container">
            <div class="profile-header">
                <h1>Профиль пользователя</h1>
                <div class="profile-img-container" onclick="document.getElementById('avatar-input').click();">
                    <img src="<?php echo htmlspecialchars($avatar_path); ?>" alt="Profile" class="profile-img">
                </div>
            </div>
            <form action="" method="POST" enctype="multipart/form-data" style="display: none;">
                <input type="file" name="avatar" id="avatar-input" accept="image/*" onchange="this.form.submit();">
            </form>
            <div class="profile-details">
                <div class="profile-info">
                    <div class="info-group">
                        <span class="info-label">Имя пользователя</span>
                        <span class="info-value"><?php echo htmlspecialchars($username); ?></span>
                    </div>
                    <div class="info-group">
                        <span class="info-label">Email</span>
                        <span class="info-value"><?php echo htmlspecialchars($email); ?></span>
                    </div>
                    <div class="info-group">
                        <span class="info-label">Роль</span>
                        <span class="info-value"><?php echo htmlspecialchars($role_name); ?></span>
                    </div>
                </div>
            </div>

            <div class="profile-details" style="margin-top: 1.5rem;">
                <h2 style="color: #2c3e50; font-size: 1.5rem; margin-bottom: 1.5rem;">Статистика</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <span class="material-symbols-rounded">task</span>
                        </div>
                        <div class="stat-info">
                            <span class="stat-value"><?php echo $total_tasks; ?></span>
                            <span class="stat-label">Всего заданий</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <span class="material-symbols-rounded">check_circle</span>
                        </div>
                        <div class="stat-info">
                            <span class="stat-value"><?php echo $solved_tasks; ?></span>
                            <span class="stat-label">Решено заданий</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <span class="material-symbols-rounded">percent</span>
                        </div>
                        <div class="stat-info">
                            <span class="stat-value"><?php echo $completion_rate; ?>%</span>
                            <span class="stat-label">Выполнено</span>
                        </div>
                    </div>
                </div>
                <div class="progress-bar">
                    <div class="progress" style="width: <?php echo $completion_rate; ?>%"></div>
                </div>

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
