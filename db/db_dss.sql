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

-- Alternatif hanya 3
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

-- ===========================
-- DATA EVALUASI MULTI-PERIODE
-- Alternatif 3, beberapa bulan & tahun
-- ===========================
INSERT INTO `saw_evaluations` (`id_alternative`, `id_criteria`, `value`, `period`) VALUES
-- Tahun 2025, Januari
(1,1,5,'2025-01'),(1,2,4,'2025-01'),(1,3,3,'2025-01'),(1,4,5,'2025-01'),(1,5,4,'2025-01'),
(2,1,3,'2025-01'),(2,2,2,'2025-01'),(2,3,4,'2025-01'),(2,4,3,'2025-01'),(2,5,2,'2025-01'),
(3,1,4,'2025-01'),(3,2,5,'2025-01'),(3,3,3,'2025-01'),(3,4,4,'2025-01'),(3,5,5,'2025-01'),

-- Tahun 2025, Februari
(1,1,4,'2025-02'),(1,2,3,'2025-02'),(1,3,4,'2025-02'),(1,4,5,'2025-02'),(1,5,4,'2025-02'),
(2,1,2,'2025-02'),(2,2,3,'2025-02'),(2,3,2,'2025-02'),(2,4,3,'2025-02'),(2,5,3,'2025-02'),
(3,1,5,'2025-02'),(3,2,4,'2025-02'),(3,3,5,'2025-02'),(3,4,4,'2025-02'),(3,5,5,'2025-02'),

-- Tahun 2025, Maret
(1,1,3,'2025-03'),(1,2,4,'2025-03'),(1,3,4,'2025-03'),(1,4,3,'2025-03'),(1,5,4,'2025-03'),
(2,1,4,'2025-03'),(2,2,3,'2025-03'),(2,3,3,'2025-03'),(2,4,4,'2025-03'),(2,5,2,'2025-03'),
(3,1,5,'2025-03'),(3,2,5,'2025-03'),(3,3,4,'2025-03'),(3,4,5,'2025-03'),(3,5,5,'2025-03'),

-- Tahun 2026, Januari
(1,1,4,'2026-01'),(1,2,4,'2026-01'),(1,3,3,'2026-01'),(1,4,4,'2026-01'),(1,5,4,'2026-01'),
(2,1,3,'2026-01'),(2,2,2,'2026-01'),(2,3,3,'2026-01'),(2,4,2,'2026-01'),(2,5,3,'2026-01'),
(3,1,5,'2026-01'),(3,2,5,'2026-01'),(3,3,5,'2026-01'),(3,4,4,'2026-01'),(3,5,5,'2026-01'),

-- Tahun 2026, Februari
(1,1,5,'2026-02'),(1,2,4,'2026-02'),(1,3,4,'2026-02'),(1,4,5,'2026-02'),(1,5,5,'2026-02'),
(2,1,4,'2026-02'),(2,2,3,'2026-02'),(2,3,3,'2026-02'),(2,4,4,'2026-02'),(2,5,3,'2026-02'),
(3,1,5,'2026-02'),(3,2,5,'2026-02'),(3,3,4,'2026-02'),(3,4,5,'2026-02'),(3,5,5,'2026-02');

-- ===========================
-- TABLE: saw_users
-- ===========================
CREATE TABLE `saw_users` (
  `id_user` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) DEFAULT NULL,
  `password` VARCHAR(150) DEFAULT NULL,
  `role` ENUM('admin','manager', 'mitra', 'master') DEFAULT 'mitra',
  PRIMARY KEY (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- DATA USER
INSERT INTO `saw_users` (`username`, `password`, `role`) VALUES
('admin', MD5('admin'), 'admin'),
('manager1', MD5('manager'), 'manager'),
('mitra1', MD5('mitra'), 'mitra'),
('master', MD5('master'), 'master');

-- ===========================
-- TRIGGER: hapus evaluasi jika alternatif dihapus
-- ===========================
DELIMITER $$

CREATE TRIGGER `hapus_evaluasi_otomatis`
AFTER DELETE ON `saw_alternatives`
FOR EACH ROW
BEGIN
  DELETE FROM `saw_evaluations` 
  WHERE `id_alternative` = OLD.id_alternative;
END $$

DELIMITER ;
