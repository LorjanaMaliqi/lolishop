-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 12, 2025 at 10:55 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `loli_shop`
--

-- --------------------------------------------------------

--
-- Table structure for table `email_replies`
--

CREATE TABLE `email_replies` (
  `id` int(11) NOT NULL,
  `original_email` varchar(255) NOT NULL,
  `reply_subject` varchar(500) NOT NULL,
  `reply_message` text NOT NULL,
  `admin_user` varchar(100) NOT NULL,
  `data_dergimit` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `email_replies`
--

INSERT INTO `email_replies` (`id`, `original_email`, `reply_subject`, `reply_message`, `admin_user`, `data_dergimit`) VALUES
(1, 'lorjanamaliqi03@gmail.com', 'RE: lorjana', 'Përshëndetje Lorjana,\r\n\r\nFaleminderit për mesazhin tuaj.\r\n\r\nNë lidhje me kërkesën tuaj, ju informojmë se...\r\n\r\nPër çdo pyetje shtesë, mos hezitoni të na kontaktoni.\r\n\r\nMe respekt,\r\nLoli Shop Team', '3', '2025-07-11 16:04:03'),
(2, 'lolimaliqi03@gmail.com', 'RE: s', 'Përshëndetje s,\r\n\r\nFaleminderit për mesazhin tuaj.\r\n\r\nNë lidhje me kërkesën tuaj, ju informojmë se...\r\n\r\nPër çdo pyetje shtesë, mos hezitoni të na kontaktoni.\r\n\r\nMe respekt,\r\nLoli Shop Team', '3', '2025-07-11 16:04:21'),
(3, 'qendresamaliqi15@gmail.com', 'RE: Faleminderit', 'Përshëndetje Desa,\r\n\r\nFaleminderit për mesazhin tuaj.\r\n\r\nNë lidhje me kërkesën tuaj, ju informojmë se...\r\n\r\nPër çdo pyetje shtesë, mos hezitoni të na kontaktoni.\r\n\r\nMe respekt,\r\nLoli Shop Team', '3', '2025-07-11 16:04:47'),
(4, 'edimaliqi@gmail.com', 'RE: Pershendtje', 'Përshëndetje Eduard,\r\n\r\nFaleminderit për mesazhin tuaj.\r\n\r\nNë lidhje me kërkesën tuaj, ju informojmë se...\r\n\r\nPër çdo pyetje shtesë, mos hezitoni të na kontaktoni.\r\n\r\nMe respekt,\r\nLoli Shop Team', '3', '2025-07-12 07:57:25');

-- --------------------------------------------------------

--
-- Table structure for table `kategorite`
--

CREATE TABLE `kategorite` (
  `Id_kategoria` int(11) NOT NULL,
  `emri` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kategorite`
--

INSERT INTO `kategorite` (`Id_kategoria`, `emri`) VALUES
(1, ' Kozmetikë '),
(2, ' Veshje për Femra'),
(3, 'Aksesorë'),
(5, 'Këpucë dhe Atlete'),
(7, 'Teknologji\r\n\r\n\r\n'),
(8, 'Libra'),
(17, 'Shtepi');

-- --------------------------------------------------------

--
-- Table structure for table `kontaktet`
--

CREATE TABLE `kontaktet` (
  `id` int(11) NOT NULL,
  `emri` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `subjekti` varchar(200) NOT NULL,
  `mesazhi` text NOT NULL,
  `data_krijimit` datetime NOT NULL DEFAULT current_timestamp(),
  `status` enum('i_ri','i_lexuar','i_pergjigjur') DEFAULT 'i_ri'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kontaktet`
--

INSERT INTO `kontaktet` (`id`, `emri`, `email`, `subjekti`, `mesazhi`, `data_krijimit`, `status`) VALUES
(2, 'Desa', 'qendresamaliqi15@gmail.com', 'Faleminderit', 'Jeni me te miret!', '2025-07-06 13:19:14', ''),
(5, 's', 'lolimaliqi03@gmail.com', 's', 's', '2025-07-11 16:39:59', ''),
(7, '1', 'qendrese.maliqi.st@uni-gjilan.net', '1', '1', '2025-07-11 17:47:16', ''),
(8, 'Eduard', 'edimaliqi@gmail.com', 'Pershendtje', 'hello te gjithe', '2025-07-12 09:54:13', '');

-- --------------------------------------------------------

--
-- Table structure for table `perdoruesit`
--

CREATE TABLE `perdoruesit` (
  `id_perdoruesi` int(11) NOT NULL,
  `emri` varchar(100) NOT NULL,
  `emaili` varchar(100) NOT NULL,
  `fjalekalimi` varchar(255) NOT NULL,
  `roli` enum('klient','admin') NOT NULL DEFAULT 'klient'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `perdoruesit`
--

INSERT INTO `perdoruesit` (`id_perdoruesi`, `emri`, `emaili`, `fjalekalimi`, `roli`) VALUES
(2, 'Test Klient', 'test@lolishop.com', 'test123', 'klient'),
(3, 'Admin Loli', 'admin@lolishop.com', 'admin123', 'admin'),
(4, 'Test Klient', 'test@lolishop.com', 'test123', 'klient'),
(5, 'loli', 'maliqi@com', 'loli123', 'klient'),
(7, 'Edi', 'edimaliqi@gmail.com', '$2y$10$3knY7tEaMXSCzwv9SPC2xerWeRORxXfn9195wfapXU/PdAffewNo2', 'klient'),
(8, 'Sala', 'sala@maliqi.com', 'sala123', 'klient');

-- --------------------------------------------------------

--
-- Table structure for table `porosite`
--

CREATE TABLE `porosite` (
  `id_porosia` int(11) NOT NULL,
  `id_perdoruesi` int(11) NOT NULL,
  `totali` decimal(10,2) NOT NULL,
  `statusi` enum('ne_pritje','konfirmuar','ne_pergatitje','derguar','dorezuar','anulluar') DEFAULT 'ne_pritje',
  `emri` varchar(100) NOT NULL,
  `mbiemri` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefoni` varchar(20) NOT NULL,
  `adresa` text NOT NULL,
  `qyteti` varchar(100) NOT NULL,
  `kodi_postal` varchar(20) DEFAULT NULL,
  `metoda_pageses` enum('para_ne_dore','karte_krediti','transfer_bankar') NOT NULL,
  `komente` text DEFAULT NULL,
  `data_krijimit` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_perditesimit` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `porosite`
--

INSERT INTO `porosite` (`id_porosia`, `id_perdoruesi`, `totali`, `statusi`, `emri`, `mbiemri`, `email`, `telefoni`, `adresa`, `qyteti`, `kodi_postal`, `metoda_pageses`, `komente`, `data_krijimit`, `data_perditesimit`) VALUES
(12, 5, 18.00, 'ne_pritje', 'b', 'b', 'maliqi@com', 's', 's', 's', 's', 'para_ne_dore', 'b', '2025-07-08 15:45:01', '2025-07-08 15:45:01'),
(13, 5, 40.00, 'ne_pritje', 'a', 'a', 'maliqi@com', 's', 's', 's', 's', 'karte_krediti', 'a', '2025-07-08 17:21:12', '2025-07-08 17:21:12'),
(14, 5, 138.00, 'ne_pritje', 'a', 'a', 'maliqi@com', 's', 's', 's', 's', 'para_ne_dore', 'a', '2025-07-08 17:25:08', '2025-07-08 17:25:08'),
(15, 5, 918.00, 'ne_pritje', 'a', 'a', 'maliqi@com', 's', 's', 's', 's', 'para_ne_dore', 'a', '2025-07-08 17:28:38', '2025-07-08 17:28:38'),
(16, 5, 120.00, 'ne_pritje', 'a', 'a', 'maliqi@com', 'a', 'a', 'a', 'a', 'para_ne_dore', 'a', '2025-07-08 19:55:16', '2025-07-08 19:55:16'),
(17, 5, 132.00, 'ne_pritje', 'a', 'a', 'maliqi@com', 'a', 'a', 'a', 'a', 'para_ne_dore', 'a', '2025-07-09 12:38:15', '2025-07-09 12:38:15'),
(18, 5, 1020.00, 'ne_pritje', 'a', 'a', 'maliqi@com', 'a', 'a', 'a', 'a', 'para_ne_dore', 'a', '2025-07-09 13:48:37', '2025-07-09 13:48:37'),
(19, 5, 138.00, 'ne_pritje', 'loli', 'loli', 'maliqi@com', '123 1234', 'Rukije Haliqi ; Rruga Qarkoe', 'Gjilan', '60000', 'karte_krediti', 'loli', '2025-07-12 07:51:07', '2025-07-12 07:51:07');

-- --------------------------------------------------------

--
-- Table structure for table `porosi_produktet`
--

CREATE TABLE `porosi_produktet` (
  `id_porosi_produkti` int(11) NOT NULL,
  `id_porosia` int(11) NOT NULL,
  `id_produkti` int(11) NOT NULL,
  `sasia` int(11) NOT NULL,
  `cmimi` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `porosi_produktet`
--

INSERT INTO `porosi_produktet` (`id_porosi_produkti`, `id_porosia`, `id_produkti`, `sasia`, `cmimi`) VALUES
(18, 12, 45, 1, 18.00),
(19, 13, 35, 1, 40.00),
(20, 14, 45, 1, 18.00),
(21, 14, 44, 1, 120.00),
(22, 15, 45, 1, 18.00),
(23, 15, 43, 1, 900.00),
(24, 16, 44, 1, 120.00),
(25, 17, 44, 1, 120.00),
(26, 17, 38, 1, 12.00),
(27, 18, 44, 1, 120.00),
(28, 18, 43, 1, 900.00),
(29, 19, 44, 1, 120.00),
(30, 19, 45, 1, 18.00);

-- --------------------------------------------------------

--
-- Table structure for table `produktet`
--

CREATE TABLE `produktet` (
  `Id_produkti` int(11) NOT NULL,
  `emri` varchar(100) NOT NULL,
  `pershkrimi` text NOT NULL,
  `cmimi` decimal(10,2) NOT NULL,
  `foto` varchar(255) NOT NULL,
  `Id_kategoria` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `produktet`
--

INSERT INTO `produktet` (`Id_produkti`, `emri`, `pershkrimi`, `cmimi`, `foto`, `Id_kategoria`) VALUES
(25, 'One Hundred Years of Solitude – Gabriel García Márquez', 'Roman magjik që ndjek historinë e familjes Buendía në qytetin imagjinar Macondo.', 11.50, 'gabriel.jpg', 8),
(26, 'The Catcher in the Rye – J.D. Salinger', 'Klasik për rininë dhe kërkimin e vetvetes përmes Holden Caulfield, një adoleshent rebel në Nju Jork.', 9.00, 'Rye.png', 8),
(27, 'Clarins', 'Set kozmetik Clarins për kujdesin e lëkurës.', 25.00, 'clarins.png', 1),
(28, 'Montblanc', 'Parfum Montblanc me aromë freskuese.', 30.00, 'montblank.png', 1),
(29, 'Prada', 'Parfum Prada me aromë luksoze dhe të rafinuar.', 55.00, 'prada.png', 1),
(30, 'Lipgloss Clarins', 'Kujdes buzësh nga Clarins, ngjyrë e ndezur dhe hidratuese.', 18.00, 'lippclarins.png', 1),
(31, 'Mascara', 'Mascara për një vështrim më të theksuar.', 20.00, 'mascara.png', 1),
(32, 'Fustan', 'Fustan elegant për çdo rast special.', 40.00, 'dresblack.png', 2),
(33, 'Xhinse', 'Xhinse të rehatshme dhe të modës së fundit.', 35.00, 'jeans.png', 2),
(34, 'Maice', 'Maice komode për përdorim të përditshëm.', 15.00, 'tshirttt.png', 2),
(35, 'Xhinse të zeza', 'Xhinse të zeza për kombinime të ndryshme.', 40.00, 'jeans black.png', 2),
(36, 'Byzylyk', 'Byzylyk stil modern për çdo event.', 15.00, 'byzylyku.png', 3),
(37, 'Çantë', 'Çantë dore praktike dhe moderne.', 20.00, 'qant.png', 3),
(38, 'Vathe', 'Vathe elegant me dizajn të thjeshtë.', 12.00, 'aksesorr.png', 3),
(39, 'Converse', 'Atlete sportive Converse për stil dhe rehati.', 60.00, 'converse.png', 5),
(40, 'Këpucë', 'Këpucë elegante me taka të mesme.', 45.01, 'kepuca.png', 5),
(41, 'Patika', 'Patika sportive për aktivitetet e përditshme.', 50.00, 'patika.png', 5),
(42, 'Laptop', 'Laptop modern me performancë të lartë.', 700.00, 'loptop.png', 7),
(43, 'Apple', 'Laptop Apple MacBook me teknologji të avancuar.', 900.00, 'apple.png', 7),
(44, 'Smartwatch Samsung', 'Ora inteligjente Samsung me funksione për shëndetin dhe njoftimet.', 120.00, 'smartwatch.png', 7),
(45, 'Libri \"Të menduarit, shpejt dhe ngadalë\"', 'Një libër bestseller që eksploron dy mënyrat e të menduarit: intuitive dhe analitike.', 18.00, 'temenduarit.jpg', 8),
(56, 'unee', 'unee', 12.00, 'converse.png', 17);

-- --------------------------------------------------------

--
-- Table structure for table `shporta`
--

CREATE TABLE `shporta` (
  `id_shporta` int(11) NOT NULL,
  `Id_perdoruesi` int(11) DEFAULT NULL,
  `Id_produkti` int(11) DEFAULT NULL,
  `sasia` int(11) DEFAULT 1,
  `data_shtuar` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `email_replies`
--
ALTER TABLE `email_replies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kategorite`
--
ALTER TABLE `kategorite`
  ADD PRIMARY KEY (`Id_kategoria`);

--
-- Indexes for table `kontaktet`
--
ALTER TABLE `kontaktet`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `perdoruesit`
--
ALTER TABLE `perdoruesit`
  ADD PRIMARY KEY (`id_perdoruesi`);

--
-- Indexes for table `porosite`
--
ALTER TABLE `porosite`
  ADD PRIMARY KEY (`id_porosia`),
  ADD KEY `id_perdoruesi` (`id_perdoruesi`);

--
-- Indexes for table `porosi_produktet`
--
ALTER TABLE `porosi_produktet`
  ADD PRIMARY KEY (`id_porosi_produkti`),
  ADD KEY `id_porosia` (`id_porosia`),
  ADD KEY `id_produkti` (`id_produkti`);

--
-- Indexes for table `produktet`
--
ALTER TABLE `produktet`
  ADD PRIMARY KEY (`Id_produkti`),
  ADD KEY `Id_kategoria` (`Id_kategoria`);

--
-- Indexes for table `shporta`
--
ALTER TABLE `shporta`
  ADD PRIMARY KEY (`id_shporta`),
  ADD UNIQUE KEY `unique_user_product` (`Id_perdoruesi`,`Id_produkti`),
  ADD KEY `id_produkti` (`Id_produkti`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `email_replies`
--
ALTER TABLE `email_replies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `kategorite`
--
ALTER TABLE `kategorite`
  MODIFY `Id_kategoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `kontaktet`
--
ALTER TABLE `kontaktet`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `perdoruesit`
--
ALTER TABLE `perdoruesit`
  MODIFY `id_perdoruesi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `porosite`
--
ALTER TABLE `porosite`
  MODIFY `id_porosia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `porosi_produktet`
--
ALTER TABLE `porosi_produktet`
  MODIFY `id_porosi_produkti` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `produktet`
--
ALTER TABLE `produktet`
  MODIFY `Id_produkti` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `shporta`
--
ALTER TABLE `shporta`
  MODIFY `id_shporta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `porosite`
--
ALTER TABLE `porosite`
  ADD CONSTRAINT `porosite_ibfk_1` FOREIGN KEY (`id_perdoruesi`) REFERENCES `perdoruesit` (`id_perdoruesi`) ON DELETE CASCADE;

--
-- Constraints for table `porosi_produktet`
--
ALTER TABLE `porosi_produktet`
  ADD CONSTRAINT `porosi_produktet_ibfk_1` FOREIGN KEY (`id_porosia`) REFERENCES `porosite` (`id_porosia`) ON DELETE CASCADE,
  ADD CONSTRAINT `porosi_produktet_ibfk_2` FOREIGN KEY (`id_produkti`) REFERENCES `produktet` (`Id_produkti`) ON DELETE CASCADE;

--
-- Constraints for table `produktet`
--
ALTER TABLE `produktet`
  ADD CONSTRAINT `produktet_ibfk_1` FOREIGN KEY (`Id_kategoria`) REFERENCES `kategorite` (`Id_kategoria`);

--
-- Constraints for table `shporta`
--
ALTER TABLE `shporta`
  ADD CONSTRAINT `shporta_ibfk_1` FOREIGN KEY (`id_perdoruesi`) REFERENCES `perdoruesit` (`id_perdoruesi`) ON DELETE CASCADE,
  ADD CONSTRAINT `shporta_ibfk_2` FOREIGN KEY (`id_produkti`) REFERENCES `produktet` (`Id_produkti`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
