-- phpMyAdmin SQL Dump
-- version 4.9.5deb1
-- https://www.phpmyadmin.net/
--
-- Хост: localhost:3306
-- Время создания: Апр 21 2020 г., 13:34
-- Версия сервера: 8.0.19-0ubuntu0.19.10.3
-- Версия PHP: 7.3.11-0ubuntu0.19.10.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `cms`
--

-- --------------------------------------------------------

--
-- Структура таблицы `users_articles`
--

CREATE TABLE `users_articles` (
  `id` smallint UNSIGNED NOT NULL,
  `idUser` smallint UNSIGNED NOT NULL,
  `idArticle` smallint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `users_articles`
--

INSERT INTO `users_articles` (`id`, `idUser`, `idArticle`) VALUES
(9, 1, 3),
(10, 1, 4),
(14, 1, 6),
(5, 2, 1),
(6, 2, 2),
(11, 2, 5),
(12, 2, 6);

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `users_articles`
--
ALTER TABLE `users_articles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idUser` (`idUser`,`idArticle`),
  ADD KEY `idArticle` (`idArticle`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `users_articles`
--
ALTER TABLE `users_articles`
  MODIFY `id` smallint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `users_articles`
--
ALTER TABLE `users_articles`
  ADD CONSTRAINT `users_articles_ibfk_1` FOREIGN KEY (`idArticle`) REFERENCES `articles` (`id`),
  ADD CONSTRAINT `users_articles_ibfk_2` FOREIGN KEY (`idUser`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;