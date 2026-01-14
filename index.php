<?php
// Стартуем сессию для хранения информации о пользователе
session_start();
include("/xampp/htdocs/SQL/config/database.php");

// Получаем роль пользователя
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

// Проверка, если пользователь уже авторизован, перенаправляем на главную страницу или профиль
if (isset($_SESSION['ID_User'])) {
    header("Location: users-profile.php"); // Или другая страница, куда нужно перенаправить авторизованного пользователя
    exit();
}

if (isset($_GET['logout'])) {
  session_unset();
  session_destroy();
  header("Location: loginka.php");
  exit();
}

// Проверяем, залогинен ли пользователь
if (!isset($_SESSION['user_id'])) {
  header("Location: loginka.php");
  exit();
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];
    $error = "";

    // Функция для проверки логина и пароля с проверкой блокировки
    function authenticateUser($conn, $query, $username, $password, $role) {
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                if (isset($user['is_blocked']) && $user['is_blocked']) {
                    $_SESSION['ban_reason'] = $user['ban_reason'];
                    header("Location: banned.php"); // Перенаправляем на страницу с причиной бана
                    exit();
                }
                // Устанавливаем сессию
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_id'] = $user['ID_User']; // ID_User должен быть из вашей таблицы
                $_SESSION['role'] = $role;
                // Перенаправление на нужную страницу в зависимости от роли
                header("Location: " . ($role === 'admin' ? "admin_panel.php" : "profile.php"));
                exit();
            } else {
                return "Неверный пароль!";
            }
        }
        return "";
    }

    // Проверка в таблице пользователей
    $error = authenticateUser($conn, "SELECT * FROM users WHERE username = ?", $username, $password, 'user');
    if (!$error) {
        $error = "Пользователь с таким логином не найден!";
    }

    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Главная</title>
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

        .profile-dropdown a .material-symbols-rounded {
            font-size: 20px;
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

        .sidebar.collapsed {
            transform: translateX(-250px);
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
            padding: 0;
            transition: margin-left 0.3s ease;
        }

        .main.full-width {
            margin-left: 0;
        }

        .kartochki {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1rem;
            padding: 1rem;
        }

        .card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            position: relative;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .karton {
            text-decoration: none;
            color: inherit;
        }

        .card-img-top {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .card-body {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .card-title {
            color: #2c3e50;
            font-size: 1.25rem;
            margin-bottom: 0.5rem;
        }

        .card-text {
            color: #7f8c8d;
            font-size: 0.9rem;
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

        .toggle-sidebar-btn.active .material-symbols-rounded {
            transform: rotate(90deg);
        }

        .sidebar.show + .main .toggle-sidebar-btn .material-symbols-rounded {
            transform: rotate(180deg);
        }

        .back-to-top {
            position: fixed;
            right: 30px;
            bottom: 30px;
            width: 40px;
            height: 40px;
            background: #3498db;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s;
        }

        .back-to-top.visible {
            opacity: 1;
            visibility: visible;
        }

        .task-status {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1;
        }

        .task-status .material-symbols-rounded {
            font-size: 18px;
            color: #2ecc71;
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
                padding: 0;
            }

            .kartochki {
                padding: 1rem;
            }

            .logo {
                font-size: 1.25rem;
            }

            .logo img {
                height: 30px;
            }

            .kartochki {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 1rem;
                padding: 0.5rem;
            }

            .card {
                margin: 0 auto;
                max-width: 400px;
                width: 100%;
            }

            .card-body {
                padding: 1rem;
            }

            .nav-profile {
                margin-left: auto;
            }

            .profile-dropdown {
                right: -10px;
            }

            .sidebar-nav {
                padding: 1.5rem;
            }

            .nav-item a {
                padding: 1rem 1.25rem;
                margin-bottom: 0.75rem;
            }

            .nav-heading {
                margin: 2rem 0 1rem;
                padding-left: 1.25rem;
            }

            .nav-profile {
                padding: 6px 12px;
            }
        }

        @media (max-width: 480px) {
            .header {
                padding: 0 0.5rem;
            }

            .kartochki {
                padding: 0.5rem;
            }

            .logo span {
                font-size: 1.1rem;
            }

            .kartochki {
                grid-template-columns: 1fr;
                padding: 0.5rem;
            }

            .card {
                margin: 0 auto;
                max-width: 100%;
            }

            .username {
                font-size: 14px;
            }

            .sidebar-nav {
                padding: 1.25rem;
            }

            .nav-item a {
                padding: 0.875rem 1rem;
            }

            .nav-heading {
                padding-left: 1rem;
            }

            .nav-profile {
                padding: 6px 8px;
            }
        }

        .back-to-top {
            position: fixed;
            right: 30px;
            bottom: 30px;
            width: 40px;
            height: 40px;
            background: #3498db;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s;
        }

        .back-to-top.visible {
            opacity: 1;
            visibility: visible;
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
                <a href="users-profile.php">
                    <span class="material-symbols-rounded">person</span>
                    Мой профиль
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
                <a href="index.php" class="active">
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
            <li class="nav-item">
                <a href="users-profile.php">
                    <span class="material-symbols-rounded">person</span>
                    Профиль
                </a>
            </li>
            <?php endif; ?>
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
        <div class="kartochki">
          <?php
          $con = mysqli_connect("localhost", "root", "", "general");
          mysqli_set_charset($con, 'utf8');

          $user_id = $_SESSION['user_id'];
          $carts_query = "
              SELECT t.*, ut.is_solved 
              FROM tasks t 
              LEFT JOIN user_tasks ut ON t.ID_Task = ut.task_id AND ut.user_id = '$user_id'
          ";
          $carts_result = mysqli_query($con, $carts_query) or die(mysqli_error($con));

          while ($carts_object = mysqli_fetch_object($carts_result)) {
          ?>
            <div class="card">
              <?php if ($carts_object->is_solved): ?>
                <div class="task-status">
                  <span class="material-symbols-rounded">check_circle</span>
                </div>
              <?php endif; ?>
              <a href="task.php?id=<?php echo $carts_object->ID_Task; ?>" class="karton">
                <img src="<?php echo $carts_object->img_task; ?>" class="card-img-top" alt="...">
                <div class="card-body">
                  <h5 class="card-title">Задание №<?php echo $carts_object->ID_Task; ?></h5>
                  <p class="card-text"><?php echo $carts_object->Name; ?></p>
                </div>
              </a>
            </div>
          <?php
          }
          ?>
        </div>
    </main>

    <a href="#" class="back-to-top">
        <span class="material-symbols-rounded">arrow_upward</span>
    </a>

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

        document.addEventListener('click', (e) => {
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
        });

        const backToTop = document.querySelector('.back-to-top');

        window.addEventListener('scroll', () => {
            if (window.scrollY > 300) {
                backToTop.classList.add('visible');
            } else {
                backToTop.classList.remove('visible');
            }
        });

        backToTop.addEventListener('click', (e) => {
            e.preventDefault();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    </script>
</body>

</html>
