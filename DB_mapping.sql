-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Počítač: 127.0.0.1:3312
-- Vytvořeno: Stř 14. srp 2019, 17:52
-- Verze serveru: 10.1.38-MariaDB-1~xenial
-- Verze PHP: 7.2.19-0ubuntu0.18.04.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Databáze: `wonocx28`
--

-- --------------------------------------------------------

--
-- Struktura tabulky `idMapping`
--

CREATE TABLE `idMapping` (
  `en_id` int(255) DEFAULT NULL,
  `de_id` int(255) DEFAULT NULL,
  `fr_id` int(255) DEFAULT NULL,
  `sp_id` int(255) DEFAULT NULL,
  `it_id` int(255) DEFAULT NULL,
  `debug_cell` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabulky `taxonomyMapping`
--

CREATE TABLE `taxonomyMapping` (
  `en_id` int(255) DEFAULT NULL,
  `de_id` int(255) DEFAULT NULL,
  `fr_id` int(255) DEFAULT NULL,
  `sp_id` int(255) DEFAULT NULL,
  `it_id` int(255) DEFAULT NULL,
  `en_parent_id` int(255) DEFAULT NULL,
  `de_parent_id` int(255) DEFAULT NULL,
  `fr_parent_id` int(255) DEFAULT NULL,
  `sp_parent_id` int(255) DEFAULT NULL,
  `it_parent_id` int(255) DEFAULT NULL,
  `en_url` text,
  `de_url` text,
  `fr_url` text,
  `sp_url` text,
  `it_url` text,
  `debug_cell` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
