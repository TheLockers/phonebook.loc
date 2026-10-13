-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Хост: MySQL-8.4
-- Время создания: Дек 24 2025 г., 22:42
-- Версия сервера: 8.4.4
-- Версия PHP: 8.4.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `phonebook`
--

-- --------------------------------------------------------

--
-- Структура таблицы `contacts`
--

CREATE TABLE `contacts` (
  `id` int NOT NULL,
  `first_name` varchar(30) NOT NULL,
  `last_name` varchar(30) NOT NULL,
  `middle_name` varchar(30) NOT NULL,
  `post_id` int NOT NULL,
  `unit_id` int DEFAULT NULL,
  `corps` varchar(100) NOT NULL,
  `office_number` varchar(50) NOT NULL,
  `internal_phone_number` varchar(20) NOT NULL,
  `landline` varchar(20) NOT NULL,
  `mobile_phone_number` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `photo` longblob,
  `is_admin` tinyint(1) DEFAULT '0',
  `password_hash` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `contacts`
--

INSERT INTO `contacts` (`id`, `first_name`, `last_name`, `middle_name`, `post_id`, `unit_id`, `corps`, `office_number`, `internal_phone_number`, `landline`, `mobile_phone_number`, `email`, `created_at`, `updated_at`, `photo`, `is_admin`, `password_hash`) VALUES
(1, 'Иван', 'Петров', 'Сергеевич', 1, 1, 'A', '501', '1001', '+74951234501', '+79161234501', 'i.petrov@company.ru', '2025-12-09 09:27:01', '2025-12-24 18:08:06', NULL, 1, '$2y$12$aCH3S68b5FHonlC03Wk1T.b7RxaVDuKdxqC/ujB.Bcw39pVlz8Rv.'),
(2, 'Мария', 'Сидорова', 'Ивановна', 2, 2, 'A', '502', '1002', '+74951234502', '+79161234502', 'm.sidorova@company.ru', '2025-12-09 09:27:01', '2025-12-24 19:26:23', NULL, 0, '$2y$12$GII2s3hY6a2sZhAIsR8zguGJKXMg.Krc.6QUndQ2kcri1MSYx6tYu'),
(3, 'Аннютка', 'Козлова', 'Александровна', 9, 2, 'B', '201', '1201', '+74951235201', '+79161235201', 'a.kozlova@company.ru', '2025-12-09 09:27:01', '2025-12-24 19:34:52', NULL, 0, '$2y$12$qfzB.KtRPi5fA8upWz7v0u/9/ZG8gJxZ04Fsu9A.IiZ6hFZr6Svs2'),
(4, 'Сергей', 'Волков', 'Дмитриевич', 9, 2, 'B', '202', '1202', '+74951235202', '+79161235202', 's.volkov@company.ru', '2025-12-09 09:27:01', '2025-12-24 18:08:08', NULL, 0, '$2y$12$HLhc809HZhqIHcTJtHJdpuvsX7GBBqz2wtmOxVXjDwDuPJnyQP7IK'),
(5, 'Алексей', 'Смирнов', 'Олегович', 3, 3, 'C', '301', '1301', '+74951236301', '+79161236301', 'a.smirnov@company.ru', '2025-12-09 09:27:01', '2025-12-24 18:08:08', NULL, 1, '$2y$12$W4X.5cy800tjJFYFbMubnOWTWM8gR5Nzy3/LS6ZbeysXuo3y2GcEe'),
(6, 'Дмитрий', 'Куценко', 'Викторович', 8, 3, 'C', '302', '1228', '+74951236302', '+79161236302', 'd.kuznetsov@company.ru', '2025-12-09 09:27:01', '2025-12-24 19:21:38', NULL, 1, '$2y$12$V5x6.VuNDiYtaITXGqriketMZi9PrWKc.toYgaH2SOPO6hE7KJMeG'),
(7, 'Елена', 'Новикова', 'Павловна', 4, 4, 'D', '101', '1101', '+74951234101', '+79161234101', 'e.novikova@company.ru', '2025-12-09 09:27:01', '2025-12-24 18:08:09', NULL, 0, '$2y$12$epo10qmfArRi2MZMdUBZhed9ts4Vs6hN2vYWpHh1ApEeKgOrQFf.i'),
(8, 'Артем', 'Морозов', 'Андреевич', 4, 4, 'D', '102', '1102', '+74951234102', '+79161234102', 'a.morozov@company.ru', '2025-12-09 09:27:01', '2025-12-24 18:08:10', NULL, 0, '$2y$12$cmRPvn1WGrtai2xqoy88K.flwgUEf8evrafVwVGNNb/bm5wgau5Em'),
(9, 'Ольга', 'Павлова', 'Сергеевна', 5, 8, 'C', '303', '1303', '+74951236303', '+79161236303', 'o.pavlova@company.ru', '2025-12-09 09:27:01', '2025-12-24 18:08:10', NULL, 0, '$2y$12$N5GWNpAm690udL/guPYSeOaFxwF.MS5rJ2GjiZpQ/QQLfXs/x/yZS'),
(10, 'Игорь', 'Семенов', 'Игоревич', 6, 8, 'C', '304', '1304', '+74951236304', '+79161236304', 'i.semenov@company.ru', '2025-12-09 09:27:01', '2025-12-24 18:08:11', NULL, 0, '$2y$12$koTFMW0UCUmdLNp3G85pmOiYqhhZQWEHdBMMm6JZ0HWEPsVspIkzi'),
(11, 'Наталья', 'Васнецова', 'Юрьевна', 7, 8, 'C', '305', '1305', '+74951236305', '+79161236305', 'n.vasnecova@company.ru', '2025-12-09 09:27:01', '2025-12-24 18:08:11', NULL, 0, '$2y$12$mmKbhWtFRvt8fVbX04CoDOboDx9C7VStS9cXAUpHjncu8F8b/.F6a'),
(12, 'Виктория', 'Федорова', 'Алексеевна', 12, 5, 'E', '401', '1401', '+74951237401', '+79161237401', 'v.fedorova@company.ru', '2025-12-09 09:27:01', '2025-12-24 18:08:12', NULL, 0, '$2y$12$t0LIjJzPotrjdsZm/IYiqu.dRvD1mmr8UFrpYW0iZxMUvTNRHDnnK'),
(13, 'Константин', 'Белов', 'Михайлович', 11, 6, 'D', '601', '1601', '+74951238601', '+79161238601', 'k.belov@company.ru', '2025-12-09 09:27:01', '2025-12-24 19:34:19', NULL, 1, '$2y$12$/lVuL3FGBDGibpWWHCWfHODe1x/FxgKdP7ohSlgbS1TirOWSiHdPu'),
(14, 'Татьяна', 'Орлова', 'Владимировна', 10, 7, 'G', '701', '1701', '+74951239701', '+79161239701', 't.orlova@company.ru', '2025-12-09 09:27:01', '2025-12-24 18:08:13', NULL, 0, '$2y$12$f3VMd21Nx2S8V.nVNuSInubLoZho9pAQ64E0E0ZFIHT/5RhTW7Xn.'),
(15, 'Роман', 'Титов', 'Анатольевич', 8, 9, 'H', '801', '1801', '+74951230801', '+79161230801', 'r.titov@company.ru', '2025-12-09 09:27:01', '2025-12-24 18:08:13', NULL, 0, '$2y$12$DhRv/9G4GmUJLrIzRdHBOeIuKnfykPrIZIN3m8glS7Sm.9X.27KDa'),
(16, 'Алиса', 'Крылова', 'Романовна', 13, 10, 'I', '901', '1901', '+74951231901', '+79161231901', 'a.krylova@company.ru', '2025-12-09 09:27:01', '2025-12-24 18:08:14', NULL, 0, '$2y$12$kk/dTshZ7Csi5wMmH96s.OwGubcZU5zXzlhjZwF.XyhkGQvSsFu.e'),
(17, 'Георгий', 'Мельников', 'Петрович', 13, 10, 'I', '902', '1902', '+74951231902', '+79161231902', 'g.melnikov@company.ru', '2025-12-09 09:27:01', '2025-12-24 18:08:14', NULL, 0, '$2y$12$K1DcXr8eFDV0VZtC4ScvVetYtuohnHVof7AkGGQhsCU.y/NjzrY5u');

-- --------------------------------------------------------

--
-- Структура таблицы `post`
--

CREATE TABLE `post` (
  `id` int NOT NULL,
  `position` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `post`
--

INSERT INTO `post` (`id`, `position`) VALUES
(1, 'Генеральный директор'),
(2, 'Финансовый директор'),
(3, 'Технический директор'),
(4, 'Менеджер по продажам'),
(5, 'Старший разработчик'),
(6, 'Разработчик'),
(7, 'Тестировщик'),
(8, 'Системный администратор'),
(9, 'Бухгалтер'),
(10, 'HR-менеджер'),
(11, 'Юрист'),
(12, 'Маркетолог'),
(13, 'Аналитик'),
(14, 'Дизайнер'),
(15, 'Секретарь');

-- --------------------------------------------------------

--
-- Структура таблицы `roles`
--

CREATE TABLE `roles` (
  `id` int NOT NULL,
  `role` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `roles`
--

INSERT INTO `roles` (`id`, `role`) VALUES
(1, 'Админ'),
(2, 'Пользователь');

-- --------------------------------------------------------

--
-- Структура таблицы `units`
--

CREATE TABLE `units` (
  `id` int NOT NULL,
  `unit_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `units`
--

INSERT INTO `units` (`id`, `unit_name`) VALUES
(1, 'Руководство'),
(2, 'Финансовый отдел'),
(3, 'IT-отдел'),
(4, 'Отдел продаж'),
(5, 'Отдел маркетинга'),
(6, 'Юридический отдел'),
(7, 'HR-отдел'),
(8, 'Отдел разработки'),
(9, 'Техническая поддержка'),
(10, 'Отдел аналитики');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `unit_id` (`unit_id`);

--
-- Индексы таблицы `post`
--
ALTER TABLE `post`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `units`
--
ALTER TABLE `units`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT для таблицы `post`
--
ALTER TABLE `post`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT для таблицы `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `units`
--
ALTER TABLE `units`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `contacts`
--
ALTER TABLE `contacts`
  ADD CONSTRAINT `contacts_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `post` (`id`),
  ADD CONSTRAINT `contacts_ibfk_2` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
