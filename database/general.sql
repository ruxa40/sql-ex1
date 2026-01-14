-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1
-- Время создания: Дек 24 2024 г., 18:00
-- Версия сервера: 10.4.32-MariaDB
-- Версия PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `general`
--

-- --------------------------------------------------------

--
-- Структура таблицы `description`
--

CREATE TABLE `description` (
  `ID_Desc` int(11) NOT NULL,
  `Description` varchar(8000) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `description`
--

INSERT INTO `description` (`ID_Desc`, `Description`) VALUES
(1, 'Схема БД состоит из четырех таблиц:Product(maker, model, type)PC(code, model, speed, ram, hd, cd, price)Laptop(code, model, speed, ram, hd, price, screen)Printer(code, model, color, type, price)'),
(2, 'Рассматривается БД кораблей, участвовавших во второй мировой войне. Имеются следующие отношения:\nClasses (class, type, country, numGuns, bore, displacement)\nShips (name, class, launched)\nBattles (name, date)\nOutcomes (ship, battle, result)');

-- --------------------------------------------------------

--
-- Структура таблицы `roles`
--

CREATE TABLE `roles` (
  `ID_Role` int(11) NOT NULL,
  `Name_role` varchar(255) NOT NULL,
  `Ban_user` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `roles`
--

INSERT INTO `roles` (`ID_Role`, `Name_role`, `Ban_user`) VALUES
(0, 'Пользователь', 0),
(1, 'Администратор', 0);

-- --------------------------------------------------------

--
-- Структура таблицы `tasks`
--

CREATE TABLE `tasks` (
  `ID_Task` int(11) NOT NULL,
  `ID_Desc` int(11) DEFAULT NULL,
  `Name` varchar(255) NOT NULL,
  `Solve` varchar(8000) DEFAULT '0',
  `img_task` varchar(100) NOT NULL DEFAULT 'assets/img/card.jpg'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `tasks`
--

INSERT INTO `tasks` (`ID_Task`, `ID_Desc`, `Name`, `Solve`, `img_task`) VALUES
(1, 1, 'Найдите номер модели, скорость и размер жесткого диска для всех ПК стоимостью менее 500$. Вывести: model, speed и hd', 'SELECT model, speed, hd \r\nFROM PC \r\nWHERE price < 500', 'assets/img/logo.svg'),
(2, 1, 'Найдите производителей принтеров. Вывести: maker', 'SELECT DISTINCT maker \r\nFROM product \r\nWHERE type = \'Printer\'', 'assets/img/logo.svg'),
(3, 1, 'Найдите номер модели, объем памяти и размеры экранов ПК-блокнотов, цена которых превышает 1000$', 'SELECT model, ram, screen \r\nFROM Laptop \r\nWHERE price > 1000', 'assets/img/logo.svg'),
(4, 1, 'Найдите все записи таблицы Printer для цветных принтеров.', 'SELECT *FROM Printer \r\nWHERE color=\'y\'', 'assets/img/logo.svg'),
(5, 1, 'Найдите номер модели, скорость и размер жесткого диска ПК, имеющих 12x или 24x CD и цену менее 600$', 'SELECT model, speed, hd \r\nFROM PC \r\nWHERE ((cd = \'12x\' OR cd = \'24x\') AND (price < 600))', 'assets/img/logo.svg'),
(6, 1, 'Для каждого производителя, выпускающего ПК-блокноты с объёмом жесткого диска не менее 10 Гбайт, найти скорости таких ПК-блокнотов.', 'SELECT DISTINCT Product.maker, Laptop.speed \r\nFROM Product, Laptop \r\nWHERE Laptop.model = Product.model \r\nAND Laptop.hd >= 10 ', 'assets/img/logo.svg'),
(7, 1, 'Найдите производителя, выпускающего ПК, но не ПК-блокноты.', 'SELECT Product.model, PC.price FROM Product \r\nJOIN PC ON Product.model = PC.model \r\nWHERE Product.maker = \'B\' \r\nUNION \r\nSELECT Product.model, Laptop.price FROM Product \r\nJOIN Laptop ON Product.model = Laptop.model \r\nWHERE Product.maker = \'B\' \r\nUNION \r\nSELECT Product.model, Printer.price FROM Product \r\nJOIN Printer ON Product.model = Printer.model \r\nWHERE Product.maker = \'B\'', 'assets/img/logo.svg'),
(8, 1, 'Найдите производителя, выпускающего ПК, но не ПК-блокноты.', 'SELECT maker \r\nFROM Product \r\nWHERE type = \'PC\' \r\nAND maker NOT IN ( \r\nSELECT maker \r\nFROM Product \r\nWHERE type = \'Laptop\' \r\n) \r\nGROUP BY maker', 'assets/img/logo.svg'),
(9, 1, 'Найдите производителей ПК с процессором не менее 450 Мгц. Вывести: Maker', 'SELECT DISTINCT Product.maker \r\nFROM Product \r\nJOIN PC ON Product.model = PC.model \r\nWHERE PC.speed >= 450 ', 'assets/img/logo.svg'),
(10, 1, 'Найдите модели принтеров, имеющих самую высокую цену. Вывести: model, price', 'SELECT model, price \r\nFROM Printer \r\nWHERE price = (SELECT MAX(price) FROM Printer)', 'assets/img/logo.svg'),
(11, 1, 'Найдите среднюю скорость ПК.', 'SELECT AVG(speed) AS Avg_speed\r\nFROM PC', 'assets/img/logo.svg'),
(12, 1, 'Найдите среднюю скорость ПК-блокнотов, цена которых превышает 1000$', 'SELECT AVG(speed) AS Avg_speed \r\nFROM Laptop \r\nWHERE Laptop.price > 1000', 'assets/img/logo.svg'),
(13, 1, 'Найдите среднюю скорость ПК, выпущенных производителем A.', 'SELECT AVG(PC.speed) AS Avg_speed \r\nFROM Product \r\nJOIN PC ON Product.model = PC.model \r\nWHERE Product.maker = \'A\'', 'assets/img/logo.svg'),
(14, 2, 'Найдите класс, имя и страну для кораблей из таблицы Ships, имеющих не менее 10 орудий.', 'SELECT Ships.class, Ships.name, Classes.country \r\nFROM Ships \r\nJOIN Classes ON Ships.class = Classes.class \r\nWHERE Classes.numGuns >= 10', 'assets/img/logo.svg'),
(15, 1, 'Найдите размеры жестких дисков, совпадающих у двух и более PC. Вывести: HD', 'SELECT hd \r\nFROM PC \r\nGROUP BY hd \r\nHAVING COUNT(*) >= 2', 'assets/img/logo.svg'),
(16, 1, 'Найдите пары моделей PC, имеющих одинаковые скорость и RAM. В результате каждая пара указывается только один раз, т.е. (i,j), но не (j,i), Порядок вывода: модель с большим номером, модель с меньшим номером, скорость и RAM', 'SELECT DISTINCT B.model AS model, A.model AS model, A.speed, A.ram \r\nFROM PC AS A, PC B \r\nWHERE A.speed = B.speed AND A.ram = B.ram AND A.model < B.model', 'assets/img/logo.svg'),
(17, 1, 'Найдите модели ПК-блокнотов, скорость которых меньше скорости каждого из ПК. Вывести: type, model, speed', 'SELECT DISTINCT type, Laptop.model, speed \r\nFROM Laptop \r\nINNER JOIN Product ON Laptop.model = Product.model \r\nWHERE speed < (SELECT MIN(speed) FROM PC)', 'assets/img/logo.svg'),
(18, 1, 'Найдите производителей самых дешевых цветных принтеров. Вывести: maker, price', 'SELECT DISTINCT maker, price \r\nFROM Product \r\nJOIN Printer ON Product.model = Printer.model \r\nWHERE price = (SELECT MIN(price)  \r\nFROM Printer \r\nWHERE color = \'y\') \r\nAND color = \'y\' ', 'assets/img/logo.svg'),
(19, 1, 'Для каждого производителя, имеющего модели в таблице Laptop, найдите средний размер экрана выпускаемых им ПК-блокнотов. Вывести: maker, средний размер экрана.', 'SELECT DISTINCT maker, AVG(screen) \r\nFROM Laptop \r\nJOIN Product ON Laptop.model = Product.model \r\nGROUP BY maker', 'assets/img/logo.svg'),
(20, 1, 'Найдите производителей, выпускающих по меньшей мере три различных модели ПК. Вывести: Maker, число моделей ПК.', 'SELECT maker, COUNT(model) \r\nFROM Product \r\nWHERE type = \'PC\' \r\nGROUP BY maker \r\nHAVING COUNT(model) >= 3 ', 'assets/img/logo.svg');

-- --------------------------------------------------------

--
-- Структура таблицы `task_log`
--

CREATE TABLE `task_log` (
  `ID_Tasklog` int(11) NOT NULL,
  `ID_User` int(11) DEFAULT NULL,
  `ID_Task` int(11) DEFAULT NULL,
  `Date_solved` datetime NOT NULL,
  `Solved` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `ID_User` int(11) NOT NULL,
  `ID_Role` int(11) DEFAULT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `ban` tinyint(1) DEFAULT 0,
  `whitelist` tinyint(1) DEFAULT 0,
  `avatar_path` varchar(255) DEFAULT 'uploads/avatars/default.jpg',
  `ban_reason` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`ID_User`, `ID_Role`, `username`, `email`, `password`, `ban`, `whitelist`, `avatar_path`, `ban_reason`) VALUES
(2, 1, 'pidor', 'kynilingusbebra@gmail.com', '$2y$10$HTf61RUFwfVY44cv4i.8vuuJ/VMrw9ODUEmxxU1drqjCH5mII93pe', 0, 1, 'uploads/avatars/67697ad1a819e_53917783684d8de60b9a8f1d83c214e2.jpg', NULL),
(5, 0, '123', '123413@g.com', '$2y$10$/tf6864kwy/teFHzOigBK.mSbU7V.6jKm83tVMsIMFOK53vWHbmHq', 0, 0, 'uploads/avatars/default.jpg', NULL),
(6, 0, '34234', 'x30n.fn@gmail.com', '$2y$10$rTm/GVhqXQFRNy3RBvmCT.5A3k4Xt02fdYt7UEaiWpx4zh6deB8qa', 0, 0, 'uploads/avatars/default.jpg', NULL),
(7, 0, '1488', 'shamshin318@gmail.com', '$2y$10$XyoXrce/Bx66n7C7XADJte9tK1GThRCAjqdRDkFs/d63oGu9VmgfW', 0, 1, 'uploads/avatars/67696e5f02f83_WIN_20240504_23_50_08_Pro.jpg', NULL),
(8, 0, '148814881488', 'mazabig05@mail.ru', '$2y$10$NQFiRHbBDHu5z7WXjjAhAuWnLCVaDjr7MeFNbkBrL5KBrFjAFQ/2i', 0, 0, 'uploads/avatars/default.jpg', NULL),
(9, 0, '1010101', '123@123.ru', '$2y$10$hgF/LYKrbX4NvACIx8BsrO9106JMGkUlAtQo6ioqVhfg3GNrCsJB2', 0, 0, 'uploads/avatars/default.jpg', NULL),
(10, 1, 'drain', 'pizda@pizda.ru', '$2y$10$yl5uY9dnTDR9/XsxVz7WGeTSj0PfpqaBFVlw8Ad5zYAJ.VHKuRvO.', 0, 1, 'uploads/avatars/676aaf5b56235_ammiak-100ml.jpg', NULL),
(11, 0, 'test', 'test@mail.ru', '$2y$10$pPVO3LVZOopgn.c2.yKq3e2SlapEZpphn5uTwZZrujr6W2TO/Sgue', 1, 0, 'uploads/avatars/default.jpg', '123'),
(12, 1, 'admin', 'cry@cry.cry', '$2y$10$/uAz4nhOoHRygEKuzGtq8en/zWkr7xiMXY1NG7rg6S7ZzPN4fCXW6', 0, 1, 'uploads/avatars/676ac520dbe78_Снимок экрана 2024-09-22 191925.png', NULL);

--
-- Триггеры `users`
--
DELIMITER $$
CREATE TRIGGER `set_whitelist_on_admin` BEFORE UPDATE ON `users` FOR EACH ROW BEGIN
    IF NEW.ID_Role = 1 AND OLD.ID_Role != 1 THEN
        SET NEW.whitelist = 1;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Структура таблицы `user_tasks`
--

CREATE TABLE `user_tasks` (
  `ID` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `is_solved` tinyint(1) DEFAULT 0,
  `solution` text DEFAULT NULL,
  `solved_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `user_tasks`
--

INSERT INTO `user_tasks` (`ID`, `user_id`, `task_id`, `is_solved`, `solution`, `solved_at`) VALUES
(6, 2, 1, 1, 'SELECT model, speed, hd \r\nFROM PC \r\nWHERE price < 500', '2024-12-24 11:46:25'),
(7, 2, 2, 1, 'SELECT DISTINCT maker \r\nFROM product \r\nWHERE type = \'Printer\'', '2024-12-24 11:46:22'),
(8, 2, 3, 1, 'SELECT model, ram, screen  FROM Laptop  WHERE price > 1000', '2024-12-24 11:37:48'),
(9, 2, 4, 1, 'SELECT *FROM Printer  WHERE color=\'y\'', '2024-12-24 11:37:49'),
(10, 2, 5, 1, 'SELECT model, speed, hd  FROM PC  WHERE ((cd = \'12x\' OR cd = \'24x\') AND (price < 600))', '2024-12-24 11:37:51'),
(11, 2, 6, 1, 'SELECT DISTINCT Product.maker, Laptop.speed  FROM Product, Laptop  WHERE Laptop.model = Product.model  AND Laptop.hd >= 10 ', '2024-12-24 11:37:53'),
(12, 2, 7, 1, 'SELECT Product.model, PC.price FROM Product  JOIN PC ON Product.model = PC.model  WHERE Product.maker = \'B\'  UNION  SELECT Product.model, Laptop.price FROM Product  JOIN Laptop ON Product.model = Laptop.model  WHERE Product.maker = \'B\'  UNION  SELECT Product.model, Printer.price FROM Product  JOIN Printer ON Product.model = Printer.model  WHERE Product.maker = \'B\'', '2024-12-24 11:38:11'),
(13, 2, 8, 1, 'SELECT maker  FROM Product  WHERE type = \'PC\'  AND maker NOT IN (  SELECT maker  FROM Product  WHERE type = \'Laptop\'  )  GROUP BY maker', '2024-12-24 11:38:20'),
(14, 2, 9, 1, 'SELECT DISTINCT Product.maker  FROM Product  JOIN PC ON Product.model = PC.model  WHERE PC.speed >= 450 ', '2024-12-24 11:38:29'),
(15, 2, 10, 1, 'SELECT model, price  FROM Printer  WHERE price = (SELECT MAX(price) FROM Printer)', '2024-12-24 11:38:52'),
(16, 2, 11, 1, 'SELECT AVG(speed) AS Avg_speed FROM PC', '2024-12-24 11:39:08'),
(17, 2, 12, 1, 'SELECT AVG(speed) AS Avg_speed  FROM Laptop  WHERE Laptop.price > 1000', '2024-12-24 11:39:31'),
(18, 2, 13, 1, 'SELECT AVG(PC.speed) AS Avg_speed  FROM Product  JOIN PC ON Product.model = PC.model  WHERE Product.maker = \'A\'', '2024-12-24 11:39:53'),
(19, 2, 14, 1, 'SELECT Ships.class, Ships.name, Classes.country  FROM Ships  JOIN Classes ON Ships.class = Classes.class  WHERE Classes.numGuns >= 10', '2024-12-24 11:40:51'),
(20, 2, 15, 1, 'SELECT hd  FROM PC  GROUP BY hd  HAVING COUNT(*) >= 2', '2024-12-24 11:40:29'),
(21, 2, 16, 1, 'SELECT DISTINCT B.model AS model, A.model AS model, A.speed, A.ram  FROM PC AS A, PC B  WHERE A.speed = B.speed AND A.ram = B.ram AND A.model < B.model', '2024-12-24 11:40:38'),
(22, 2, 17, 1, 'SELECT DISTINCT type, Laptop.model, speed  FROM Laptop  INNER JOIN Product ON Laptop.model = Product.model  WHERE speed < (SELECT MIN(speed) FROM PC)', '2024-12-24 11:41:20'),
(23, 2, 18, 1, 'SELECT DISTINCT maker, price  FROM Product  JOIN Printer ON Product.model = Printer.model  WHERE price = (SELECT MIN(price)   FROM Printer  WHERE color = \'y\')  AND color = \'y\' ', '2024-12-24 11:41:26'),
(24, 2, 19, 1, 'SELECT DISTINCT maker, AVG(screen)  FROM Laptop  JOIN Product ON Laptop.model = Product.model  GROUP BY maker', '2024-12-24 11:41:37'),
(25, 2, 20, 1, 'SELECT maker, COUNT(model)  FROM Product  WHERE type = \'PC\'  GROUP BY maker  HAVING COUNT(model) >= 3 ', '2024-12-24 11:42:34'),
(27, 10, 1, 0, 'product(maker,model,type)pc(code,model,speed,ram,hd,cd,price)laptop(code,model,speed,ram,hd,price,screen)printer(code,model,color,type,price)', '2024-12-24 12:56:27'),
(28, 12, 1, 1, 'SELECT model, speed, hd \r\nFROM PC \r\nWHERE price < 500', '2024-12-24 16:48:34');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `description`
--
ALTER TABLE `description`
  ADD PRIMARY KEY (`ID_Desc`);
ALTER TABLE `description` ADD FULLTEXT KEY `Description` (`Description`);

--
-- Индексы таблицы `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`ID_Role`);

--
-- Индексы таблицы `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`ID_Task`),
  ADD KEY `ID_Desc` (`ID_Desc`);

--
-- Индексы таблицы `task_log`
--
ALTER TABLE `task_log`
  ADD PRIMARY KEY (`ID_Tasklog`),
  ADD KEY `ID_User` (`ID_User`),
  ADD KEY `ID_Task` (`ID_Task`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`ID_User`),
  ADD UNIQUE KEY `Email` (`email`),
  ADD KEY `ID_Role` (`ID_Role`);

--
-- Индексы таблицы `user_tasks`
--
ALTER TABLE `user_tasks`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `task_id` (`task_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `description`
--
ALTER TABLE `description`
  MODIFY `ID_Desc` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `roles`
--
ALTER TABLE `roles`
  MODIFY `ID_Role` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `tasks`
--
ALTER TABLE `tasks`
  MODIFY `ID_Task` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT для таблицы `task_log`
--
ALTER TABLE `task_log`
  MODIFY `ID_Tasklog` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `ID_User` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT для таблицы `user_tasks`
--
ALTER TABLE `user_tasks`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`ID_Desc`) REFERENCES `description` (`ID_Desc`);

--
-- Ограничения внешнего ключа таблицы `task_log`
--
ALTER TABLE `task_log`
  ADD CONSTRAINT `task_log_ibfk_1` FOREIGN KEY (`ID_User`) REFERENCES `users` (`ID_User`),
  ADD CONSTRAINT `task_log_ibfk_2` FOREIGN KEY (`ID_Task`) REFERENCES `tasks` (`ID_Task`);

--
-- Ограничения внешнего ключа таблицы `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`ID_Role`) REFERENCES `roles` (`ID_Role`);

--
-- Ограничения внешнего ключа таблицы `user_tasks`
--
ALTER TABLE `user_tasks`
  ADD CONSTRAINT `user_tasks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`ID_User`),
  ADD CONSTRAINT `user_tasks_ibfk_2` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`ID_Task`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
