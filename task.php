<?php
session_start();


// Проверка авторизации пользователя
if (!isset($_SESSION['user_id'])) {
    header("Location: loginka.php");
    exit();
}

// Подключение к базе данных
$con = mysqli_connect("localhost", "root", "", "general");
mysqli_set_charset($con, 'utf8');

// Получение информации о пользователе
$user_id = $_SESSION['user_id'];
$user_query = "SELECT Username FROM users WHERE ID_User = '$user_id'";
$user_result = mysqli_query($con, $user_query) or die(mysqli_error($con));
$user = mysqli_fetch_assoc($user_result);

// Проверяем, передан ли параметр id
if (isset($_GET['id'])) {
    $id = (int) $_GET['id']; // Приводим к числу для безопасности
} else {
    die("ID задания не передан.");
}

// Запрос к базе данных
$task_query = "SELECT DISTINCT tasks.ID_Task, tasks.Name, description.Description, tasks.solve 
                FROM tasks 
                JOIN description ON tasks.ID_Desc = description.ID_Desc 
                WHERE tasks.ID_Task = '$id'";
$task_result = mysqli_query($con, $task_query) or die(mysqli_error($con));
$task_object = mysqli_fetch_object($task_result);

// Проверяем, есть ли данные и форматируем описание
if (!empty($task_object->Description)) {
    $formatted_description = str_replace(
        ['Product', 'PC', 'Laptop', 'Printer'],
        ['<br><b>Product</b>', '<br><b>PC</b>', '<br><b>Laptop</b>', '<br><b>Printer</b>'],
        $task_object->Description
    );
    $formatted_description = nl2br($formatted_description); // Преобразуем переносы строк в HTML
} else {
    $formatted_description = "<p>No description available.</p>";
}

// Проверка существующего решения пользователя
$user_solution_query = "SELECT solution FROM user_tasks WHERE user_id = '$user_id' AND task_id = '$id'";
$user_solution_result = mysqli_query($con, $user_solution_query) or die(mysqli_error($con));
$user_solution = mysqli_fetch_assoc($user_solution_result)['solution'] ?? '';

// Проверка ответа пользователя
$message = "";
$next_task_id = $id + 1; // ID следующего задания
$show_next_button = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $user_answer = trim($_POST['user_answer']); // Убираем ��робелы по краям
  $user_answer = strtolower($user_answer); // Приводим к нижнему регистру
  $user_answer = preg_replace('/\s+/', '', $user_answer); // Убираем все пробелы и переносы строк

  // Определяем правильный ответ
  $correct_answer = strtolower($task_object->solve); // Приводим правильный ответ к нижнему регистру
  $correct_answer = preg_replace('/\s+/', '', $correct_answer); // Убираем все пробелы и переносы строк

  if ($user_answer === $correct_answer) {
      $message = "<p class='text-success'>Ответ правильный!</p>";
      $show_next_button = true; // Показываем кнопку "Перейти к следующему заданию"

      // Экранируем значение solve
      $escaped_solve = mysqli_real_escape_string($con, $task_object->solve);

      // Перенос правильного ответа из tasks.solve в user_tasks.solution
      $check_solution_query = "SELECT * FROM user_tasks WHERE user_id = '$user_id' AND task_id = '$id'";
      $check_solution_result = mysqli_query($con, $check_solution_query) or die(mysqli_error($con));

      if (mysqli_num_rows($check_solution_result) > 0) {
          $update_solution_query = "
              UPDATE user_tasks 
              SET solution = '$escaped_solve', is_solved = 1, solved_at = NOW() 
              WHERE user_id = '$user_id' AND task_id = '$id'
          ";
          mysqli_query($con, $update_solution_query) or die(mysqli_error($con));
      } else {
          $insert_solution_query = "
              INSERT INTO user_tasks (user_id, task_id, solution, is_solved, solved_at) 
              VALUES ('$user_id', '$id', '$escaped_solve', 1, NOW())
          ";
          mysqli_query($con, $insert_solution_query) or die(mysqli_error($con));
      }
  } else {
      $message = "<p class='text-danger'>Ответ неверный. Попробуйте снова.</p>";

  }
}


?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Задание №<?php echo $task_object->ID_Task; ?></title>
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

        .task-container {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            backdrop-filter: blur(10px);
        }

        .task-header {
            margin-bottom: 2rem;
        }

        .task-header h4 {
            color: #2c3e50;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .task-header h5 {
            color: #34495e;
            font-size: 1.25rem;
            font-weight: 500;
        }

        .task-description {
            color: #2c3e50;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .task-description b {
            color: #3498db;
            font-weight: 600;
        }

        .task-form textarea {
            width: 100%;
            min-height: 300px;
            padding: 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-family: monospace;
            font-size: 14px;
            line-height: 1.6;
            resize: vertical;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
            margin-bottom: 1rem;
        }

        .task-form textarea:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }

        .button {
            display: inline-flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            gap: 8px;
        }

        .button-primary {
            background: #3498db;
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

        .message {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .message-success {
            background: rgba(46, 204, 113, 0.1);
            color: #27ae60;
            border: 1px solid rgba(46, 204, 113, 0.2);
        }

        .message-error {
            background: rgba(231, 76, 60, 0.1);
            color: #c0392b;
            border: 1px solid rgba(231, 76, 60, 0.2);
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

            .task-container {
                padding: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .header {
                padding: 0 0.5rem;
            }

            .task-container {
                padding: 1rem;
            }

            .task-header h4 {
                font-size: 1.25rem;
            }

            .task-header h5 {
                font-size: 1rem;
            }

            .button {
                width: 100%;
                justify-content: center;
                margin-bottom: 0.5rem;
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
                <a href="index.php" class="active">
                    <span class="material-symbols-rounded">grid_view</span>
                    Задания
                </a>
            </li>
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
        <div class="task-container">
            <div class="task-header">
                <h4>Задание №<?php echo $task_object->ID_Task; ?></h4>
                <h5><?php echo htmlspecialchars($task_object->Name); ?></h5>
            </div>
            <div class="task-description">
                <?php echo $formatted_description; ?>
            </div>
            <?php if (!empty($message)): ?>
                <div class="message <?php echo strpos($message, 'правильный') !== false ? 'message-success' : 'message-error'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            <form method="POST" action="" class="task-form">
                <textarea name="user_answer" placeholder="Введите ваше решение здесь..."><?php echo htmlspecialchars($user_solution); ?></textarea>
                <button type="submit" class="button button-primary">
                    <span class="material-symbols-rounded">play_arrow</span>
                    Выполнить
                </button>
                <?php if ($show_next_button): ?>
                    <a href="task.php?id=<?php echo $next_task_id; ?>" class="button button-success">
                        <span class="material-symbols-rounded">arrow_forward</span>
                        Следующее задание
                    </a>
                <?php endif; ?>
            </form>
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
