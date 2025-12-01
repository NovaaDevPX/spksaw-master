DROP DATABASE IF EXISTS db_dss;
CREATE DATABASE db_dss;
USE db_dss;

-- ===========================
-- TABLE: saw_alternatives
-- ===========================
CREATE TABLE `saw_alternatives` (
  `id_alternative` SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id_alternative`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `saw_alternatives` (`id_alternative`, `name`) VALUES
(1, 'PT Cinta Abadi'),
(2, 'PT Alternate'),
(3, 'PT Zeta');

-- ===========================
-- TABLE: saw_criterias
-- ===========================
CREATE TABLE `saw_criterias` (
  `id_criteria` TINYINT(3) UNSIGNED NOT NULL,
  `criteria` VARCHAR(100) NOT NULL,
  `weight` FLOAT NOT NULL,
  `attribute` ENUM('benefit','cost') DEFAULT NULL,
  PRIMARY KEY (`id_criteria`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `saw_criterias` (`id_criteria`, `criteria`, `weight`, `attribute`) VALUES
(1, 'Kualitas Produk', 25, 'benefit'),
(2, 'Pelayanan Pelanggan', 10, 'benefit'),
(3, 'Inovasi Teknologi', 15, 'benefit'),
(4, 'Harga Produk', 25, 'benefit'),
(5, 'Waktu Pengiriman', 25, 'benefit');

-- ===========================
-- TABLE: saw_evaluations
-- ===========================
CREATE TABLE `saw_evaluations` (
  `id_eval` INT NOT NULL AUTO_INCREMENT,
  `id_alternative` SMALLINT(5) UNSIGNED NOT NULL,
  `id_criteria` TINYINT(3) UNSIGNED NOT NULL,
  `value` FLOAT NOT NULL CHECK (`value` >= 0 AND `value` <= 5),
  `period` CHAR(7) NOT NULL COMMENT 'Format: YYYY-MM',
  PRIMARY KEY (`id_eval`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Contoh data periode Januari 2025
INSERT INTO `saw_evaluations` (`id_alternative`, `id_criteria`, `value`, `period`) VALUES
(1, 1, 5, '2025-01'),
(1, 2, 1, '2025-01'),
(1, 3, 3, '2025-01'),
(1, 4, 5, '2025-01'),
(1, 5, 3, '2025-01'),

(2, 1, 1, '2025-01'),
(2, 2, 2, '2025-01'),
(2, 3, 4, '2025-01'),
(2, 4, 1, '2025-01'),
(2, 5, 2, '2025-01'),

(3, 1, 3, '2025-01'),
(3, 2, 4, '2025-01'),
(3, 3, 2, '2025-01'),
(3, 4, 3, '2025-01'),
(3, 5, 5, '2025-01');

-- ===========================
-- TABLE: saw_users
-- ===========================
CREATE TABLE `saw_users` (
  `id_user` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) DEFAULT NULL,
  `password` VARCHAR(150) DEFAULT NULL,
  `role` ENUM('admin','alternatif') DEFAULT 'alternatif',
  PRIMARY KEY (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `saw_users` (`id_user`, `username`, `password`, `role`) VALUES
(1, 'admin', '21232f297a57a5a743894a0e4a801fc3', 'admin'),
(2, 'alternatif', '827ccb0eea8a706c4c34a16891f84e7b', 'alternatif');

-- ===========================
-- TRIGGER: hapus evaluasi jika alternatif dihapus
-- ===========================
DELIMITER $$

CREATE TRIGGER `hapus_evaluasi_otomatis`
AFTER DELETE ON `saw_alternatives`
FOR EACH ROW
BEGIN
  DELETE FROM `saw_evaluations` WHERE `id_alternative` = OLD.id_alternative;
END $$

DELIMITER ;

