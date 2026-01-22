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
  `id_criteria` TINYINT(3) UNSIGNED NOT NULL AUTO_INCREMENT,
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
  PRIMARY KEY (`id_eval`),

  -- FOREIGN KEY (akan aktif setelah tabel siap)
  KEY `fk_eval_alt_idx` (`id_alternative`),
  KEY `fk_eval_criteria_idx` (`id_criteria`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


-- ===========================
-- DATA EVALUASI MULTI-PERIODE
-- ===========================
-- INSERT INTO `saw_evaluations` (`id_alternative`, `id_criteria`, `value`, `period`) VALUES
-- -- =========================== 2024-01
-- (1,1,5,'2024-01'),(1,2,4,'2024-01'),(1,3,4,'2024-01'),(1,4,5,'2024-01'),(1,5,4,'2024-01'),
-- (2,1,3,'2024-01'),(2,2,2,'2024-01'),(2,3,3,'2024-01'),(2,4,3,'2024-01'),(2,5,2,'2024-01'),
-- (3,1,5,'2024-01'),(3,2,5,'2024-01'),(3,3,4,'2024-01'),(3,4,5,'2024-01'),(3,5,5,'2024-01'),

-- -- =========================== 2024-02
-- (1,1,4,'2024-02'),(1,2,4,'2024-02'),(1,3,4,'2024-02'),(1,4,4,'2024-02'),(1,5,4,'2024-02'),
-- (2,1,3,'2024-02'),(2,2,3,'2024-02'),(2,3,2,'2024-02'),(2,4,3,'2024-02'),(2,5,3,'2024-02'),
-- (3,1,5,'2024-02'),(3,2,5,'2024-02'),(3,3,5,'2024-02'),(3,4,5,'2024-02'),(3,5,5,'2024-02'),

-- -- =========================== 2024-03
-- (1,1,4,'2024-03'),(1,2,3,'2024-03'),(1,3,4,'2024-03'),(1,4,4,'2024-03'),(1,5,4,'2024-03'),
-- (2,1,3,'2024-03'),(2,2,3,'2024-03'),(2,3,3,'2024-03'),(2,4,3,'2024-03'),(2,5,2,'2024-03'),
-- (3,1,5,'2024-03'),(3,2,5,'2024-03'),(3,3,4,'2024-03'),(3,4,5,'2024-03'),(3,5,5,'2024-03'),

-- -- =========================== 2024-04
-- (1,1,4,'2024-04'),(1,2,3,'2024-04'),(1,3,4,'2024-04'),(1,4,4,'2024-04'),(1,5,4,'2024-04'),
-- (2,1,3,'2024-04'),(2,2,2,'2024-04'),(2,3,3,'2024-04'),(2,4,3,'2024-04'),(2,5,3,'2024-04'),
-- (3,1,5,'2024-04'),(3,2,5,'2024-04'),(3,3,5,'2024-04'),(3,4,5,'2024-04'),(3,5,5,'2024-04'),


-- -- =========================== 2025-09
-- (1,1,4,'2025-09'),(1,2,4,'2025-09'),(1,3,3,'2025-09'),(1,4,4,'2025-09'),(1,5,4,'2025-09'),
-- (2,1,3,'2025-09'),(2,2,3,'2025-09'),(2,3,2,'2025-09'),(2,4,3,'2025-09'),(2,5,3,'2025-09'),
-- (3,1,5,'2025-09'),(3,2,5,'2025-09'),(3,3,5,'2025-09'),(3,4,5,'2025-09'),(3,5,5,'2025-09'),

-- -- =========================== 2025-10
-- (1,1,5,'2025-10'),(1,2,4,'2025-10'),(1,3,4,'2025-10'),(1,4,5,'2025-10'),(1,5,5,'2025-10'),
-- (2,1,3,'2025-10'),(2,2,3,'2022025-10'),(2,3,3,'2025-10'),(2,4,3,'2025-10'),(2,5,3,'2025-10'),
-- (3,1,5,'2025-10'),(3,2,5,'2025-10'),(3,3,5,'2025-10'),(3,4,5,'2025-10'),(3,5,5,'2025-10'),

-- -- =========================== 2025-11
-- (1,1,5,'2025-11'),(1,2,4,'2025-11'),(1,3,4,'2025-11'),(1,4,4,'2025-11'),(1,5,5,'2025-11'),
-- (2,1,3,'2025-11'),(2,2,3,'2025-11'),(2,3,2,'2025-11'),(2,4,3,'2025-11'),(2,5,3,'2025-11'),
-- (3,1,5,'2025-11'),(3,2,5,'2025-11'),(3,3,5,'2025-11'),(3,4,5,'2025-11'),(3,5,5,'2025-11'),

-- -- =========================== 2025-12
-- (1,1,5,'2025-12'),(1,2,4,'2025-12'),(1,3,4,'2025-12'),(1,4,5,'2025-12'),(1,5,5,'2025-12'),
-- (2,1,3,'2025-12'),(2,2,2,'2025-12'),(2,3,3,'2025-12'),(2,4,3,'2025-12'),(2,5,3,'2025-12'),
-- (3,1,5,'2025-12'),(3,2,5,'2025-12'),(3,3,5,'2025-12'),(3,4,5,'2025-12'),(3,5,5,'2025-12');



-- ===========================
-- TABLE: saw_users
-- ===========================
CREATE TABLE `saw_users` (
  `id_user` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) DEFAULT NULL,
  `password` VARCHAR(150) DEFAULT NULL,
  `role` ENUM('admin','manager','mitra','quality_control') DEFAULT 'mitra',
  PRIMARY KEY (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- DATA USER
INSERT INTO `saw_users` (`username`, `password`, `role`) VALUES
('admin', MD5('admin'), 'admin'),
('manager1', MD5('manager'), 'manager'),
('mitra1', MD5('mitra'), 'mitra'),
('quality_control', MD5('quality_control'), 'quality_control');


-- ===========================
-- FOREIGN KEY 
-- ===========================
ALTER TABLE `saw_evaluations`
ADD CONSTRAINT `fk_eval_alternative`
  FOREIGN KEY (`id_alternative`) REFERENCES `saw_alternatives` (`id_alternative`)
  ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `saw_evaluations`
ADD CONSTRAINT `fk_eval_criteria`
  FOREIGN KEY (`id_criteria`) REFERENCES `saw_criterias` (`id_criteria`)
  ON DELETE CASCADE ON UPDATE CASCADE;


-- ===========================
-- TRIGGER: hapus evaluasi jika alternatif dihapus
-- (Sebenarnya tidak wajib karena FK sudah CASCADE)
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
