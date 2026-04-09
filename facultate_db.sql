-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 09, 2026 at 01:28 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `facultate_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `discipline`
--

CREATE TABLE `discipline` (
  `id_disciplina` int(11) NOT NULL,
  `denumire` varchar(100) NOT NULL,
  `an_studiu` int(1) NOT NULL,
  `semestru` int(1) DEFAULT NULL CHECK (`semestru` in (1,2)),
  `credite` int(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `discipline`
--

INSERT INTO `discipline` (`id_disciplina`, `denumire`, `an_studiu`, `semestru`, `credite`) VALUES
(1, 'Proiectarea Aplicatiilor Web', 2, 2, 5);

-- --------------------------------------------------------

--
-- Table structure for table `istoric`
--

CREATE TABLE `istoric` (
  `id_istoric` int(11) NOT NULL,
  `id_utilizator` int(11) DEFAULT NULL,
  `actiune` varchar(50) NOT NULL,
  `detalii` text NOT NULL,
  `data_ora` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `istoric`
--

INSERT INTO `istoric` (`id_istoric`, `id_utilizator`, `actiune`, `detalii`, `data_ora`) VALUES
(1, 1, 'INITIALIZARE DB', 'Baza de date a fost creata si populata cu inregistrari de test.', '2026-03-27 10:12:44'),
(2, 1, 'ADAUGARE PROFESOR', 'A adaugat profesorul: Ion Rdu', '2026-03-27 10:38:56');

-- --------------------------------------------------------

--
-- Table structure for table `note`
--

CREATE TABLE `note` (
  `id_nota` int(11) NOT NULL,
  `id_student` int(11) DEFAULT NULL,
  `id_disciplina` int(11) DEFAULT NULL,
  `id_profesor` int(11) DEFAULT NULL,
  `valoare` int(2) NOT NULL CHECK (`valoare` between 1 and 10),
  `data_notarii` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `note`
--

INSERT INTO `note` (`id_nota`, `id_student`, `id_disciplina`, `id_profesor`, `valoare`, `data_notarii`) VALUES
(1, 2, 1, 4, 10, '2026-03-27');

-- --------------------------------------------------------

--
-- Table structure for table `profesori`
--

CREATE TABLE `profesori` (
  `id_profesor` int(11) NOT NULL,
  `nume` varchar(50) NOT NULL,
  `prenume` varchar(50) NOT NULL,
  `departament` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `profesori`
--

INSERT INTO `profesori` (`id_profesor`, `nume`, `prenume`, `departament`) VALUES
(4, 'Popescu', 'Ion', 'Calculatoare'),
(5, 'Ion', 'Rdu', 'Calculatoare');

-- --------------------------------------------------------

--
-- Table structure for table `profesori_discipline`
--

CREATE TABLE `profesori_discipline` (
  `id_profesor` int(11) NOT NULL,
  `id_disciplina` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `profesori_discipline`
--

INSERT INTO `profesori_discipline` (`id_profesor`, `id_disciplina`) VALUES
(4, 1);

-- --------------------------------------------------------

--
-- Table structure for table `studenti`
--

CREATE TABLE `studenti` (
  `id_student` int(11) NOT NULL,
  `matricol` varchar(20) NOT NULL,
  `nume` varchar(50) NOT NULL,
  `prenume` varchar(50) NOT NULL,
  `specializare` varchar(10) NOT NULL,
  `an_studiu` int(1) DEFAULT NULL CHECK (`an_studiu` between 1 and 4),
  `grupa` varchar(10) NOT NULL,
  `finantare` enum('buget','taxa') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `studenti`
--

INSERT INTO `studenti` (`id_student`, `matricol`, `nume`, `prenume`, `specializare`, `an_studiu`, `grupa`, `finantare`) VALUES
(2, '10245', 'Petrea', 'Andrei', 'CTI', 2, '302', 'buget'),
(3, '10246', 'Ionescu', 'Maria', 'AIA', 3, '301', 'taxa');

-- --------------------------------------------------------

--
-- Table structure for table `utilizatori`
--

CREATE TABLE `utilizatori` (
  `id_utilizator` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `parola` varchar(255) NOT NULL,
  `rol` enum('admin','profesor','student') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `utilizatori`
--

INSERT INTO `utilizatori` (`id_utilizator`, `username`, `parola`, `rol`) VALUES
(1, 'admin', '$2y$10$VL8ET6nVUaWo9bzr0JhOCujhmyqwg8X8IF089RsyH6oEWcEKLj9Qu', 'admin'),
(2, 'petrea.andrei', '$2y$10$VL8ET6nVUaWo9bzr0JhOCujhmyqwg8X8IF089RsyH6oEWcEKLj9Qu', 'student'),
(3, 'ionescu.maria', '$2y$10$VL8ET6nVUaWo9bzr0JhOCujhmyqwg8X8IF089RsyH6oEWcEKLj9Qu', 'student'),
(4, 'popescu.ion', '$2y$10$VL8ET6nVUaWo9bzr0JhOCujhmyqwg8X8IF089RsyH6oEWcEKLj9Qu', 'profesor'),
(5, 'ion.rdu', '$2y$10$VL8ET6nVUaWo9bzr0JhOCujhmyqwg8X8IF089RsyH6oEWcEKLj9Qu', 'profesor');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `discipline`
--
ALTER TABLE `discipline`
  ADD PRIMARY KEY (`id_disciplina`);

--
-- Indexes for table `istoric`
--
ALTER TABLE `istoric`
  ADD PRIMARY KEY (`id_istoric`),
  ADD KEY `id_utilizator` (`id_utilizator`);

--
-- Indexes for table `note`
--
ALTER TABLE `note`
  ADD PRIMARY KEY (`id_nota`),
  ADD KEY `id_student` (`id_student`),
  ADD KEY `id_disciplina` (`id_disciplina`),
  ADD KEY `id_profesor` (`id_profesor`);

--
-- Indexes for table `profesori`
--
ALTER TABLE `profesori`
  ADD PRIMARY KEY (`id_profesor`);

--
-- Indexes for table `profesori_discipline`
--
ALTER TABLE `profesori_discipline`
  ADD PRIMARY KEY (`id_profesor`,`id_disciplina`),
  ADD KEY `id_disciplina` (`id_disciplina`);

--
-- Indexes for table `studenti`
--
ALTER TABLE `studenti`
  ADD PRIMARY KEY (`id_student`),
  ADD UNIQUE KEY `matricol` (`matricol`);

--
-- Indexes for table `utilizatori`
--
ALTER TABLE `utilizatori`
  ADD PRIMARY KEY (`id_utilizator`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `discipline`
--
ALTER TABLE `discipline`
  MODIFY `id_disciplina` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `istoric`
--
ALTER TABLE `istoric`
  MODIFY `id_istoric` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `note`
--
ALTER TABLE `note`
  MODIFY `id_nota` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `utilizatori`
--
ALTER TABLE `utilizatori`
  MODIFY `id_utilizator` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `istoric`
--
ALTER TABLE `istoric`
  ADD CONSTRAINT `istoric_ibfk_1` FOREIGN KEY (`id_utilizator`) REFERENCES `utilizatori` (`id_utilizator`) ON DELETE SET NULL;

--
-- Constraints for table `note`
--
ALTER TABLE `note`
  ADD CONSTRAINT `note_ibfk_1` FOREIGN KEY (`id_student`) REFERENCES `studenti` (`id_student`) ON DELETE CASCADE,
  ADD CONSTRAINT `note_ibfk_2` FOREIGN KEY (`id_disciplina`) REFERENCES `discipline` (`id_disciplina`) ON DELETE CASCADE,
  ADD CONSTRAINT `note_ibfk_3` FOREIGN KEY (`id_profesor`) REFERENCES `profesori` (`id_profesor`) ON DELETE CASCADE;

--
-- Constraints for table `profesori`
--
ALTER TABLE `profesori`
  ADD CONSTRAINT `profesori_ibfk_1` FOREIGN KEY (`id_profesor`) REFERENCES `utilizatori` (`id_utilizator`) ON DELETE CASCADE;

--
-- Constraints for table `profesori_discipline`
--
ALTER TABLE `profesori_discipline`
  ADD CONSTRAINT `profesori_discipline_ibfk_1` FOREIGN KEY (`id_profesor`) REFERENCES `profesori` (`id_profesor`) ON DELETE CASCADE,
  ADD CONSTRAINT `profesori_discipline_ibfk_2` FOREIGN KEY (`id_disciplina`) REFERENCES `discipline` (`id_disciplina`) ON DELETE CASCADE;

--
-- Constraints for table `studenti`
--
ALTER TABLE `studenti`
  ADD CONSTRAINT `studenti_ibfk_1` FOREIGN KEY (`id_student`) REFERENCES `utilizatori` (`id_utilizator`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
