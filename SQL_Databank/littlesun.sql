-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Gegenereerd op: 21 apr 2024 om 19:45
-- Serverversie: 5.7.24
-- PHP-versie: 8.0.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `littlesun`
--

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `account`
--

CREATE TABLE `account` (
  `id` int(11) NOT NULL,
  `username` varchar(300) NOT NULL,
  `email` varchar(300) NOT NULL,
  `profilePicture` varchar(300) NOT NULL,
  `password` varchar(300) NOT NULL,
  `role` varchar(300) NOT NULL,
  `hubname` varchar(300) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Gegevens worden geëxporteerd voor tabel `account`
--

INSERT INTO `account` (`id`, `username`, `email`, `profilePicture`, `password`, `role`, `hubname`) VALUES
(4, 'admin', '', '', '$2y$12$SGVUZsXsX9oLJ3x8U0jZzOTZ6tQNx3fzeJZLMeXQ2fpaEn0KNKtJy', 'admin', ''),
(23, 'Yann', 'Tagakouyann@gmail.com', 'uploads/Group_images.png', '$2y$12$qWTkcqvu/G8tS5RZncp8cuQmjDn88F6HJ7o9mEd08mD9DoD//w.7q', 'Manager', 'BMB'),
(25, 'Dante', 'Verbiestdante@gmail.com', 'uploads/Group_images.png', '$2y$12$tHC4VVKnQluGWHFkxYmnYuz0Fh2ehkr2n0ryqTUdNAxBJhbI29PfC', 'personal', 'Sweden in Dormville');

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `hub`
--

CREATE TABLE `hub` (
  `id` int(11) NOT NULL,
  `hubname` varchar(300) NOT NULL,
  `hublocation` varchar(300) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Gegevens worden geëxporteerd voor tabel `hub`
--

INSERT INTO `hub` (`id`, `hubname`, `hublocation`) VALUES
(30, 'Ethipia Green Service', 'Ethiopiastreet 14, Ethiopia'),
(32, 'Sweden in Dormville', 'Dormville 15, Dormville');

--
-- Indexen voor geëxporteerde tabellen
--

--
-- Indexen voor tabel `account`
--
ALTER TABLE `account`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `hub`
--
ALTER TABLE `hub`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT voor geëxporteerde tabellen
--

--
-- AUTO_INCREMENT voor een tabel `account`
--
ALTER TABLE `account`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT voor een tabel `hub`
--
ALTER TABLE `hub`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
