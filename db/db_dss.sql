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

INSERT INTO saw_alternatives (id_alternative, name) VALUES
(1, 'PT Pura Barutama'),
(2, 'PT Jasuindo Tiga Perkasa'),
(3, 'PT Wahyu Kartumasindo International'),
(4, 'PT Cipta Srigati Lestari'),
(5, 'PT Dua Permata Sejati'),
(6, 'PT E-Motion Entertainment'),
(7, 'PT Kinarya Selaras Piranti'),
(8, 'PT Adhi Nata Karya'),
(9, 'PT Thales DIS Indonesia'),
(10, 'PT Idemia Technologies Indonesia');

-- ===========================
-- TABLE: saw_users
-- ===========================
CREATE TABLE `saw_users` (
  `id_user` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50),
  `password` VARCHAR(150),
  `role` ENUM('admin','manager','mitra','quality_control') DEFAULT 'mitra',
  PRIMARY KEY (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `saw_users` (`username`, `password`, `role`) VALUES
('admin', MD5('admin'), 'admin'),
('manager1', MD5('manager'), 'manager'),
('mitra1', MD5('mitra'), 'mitra'),
('quality_control', MD5('quality_control'), 'quality_control');

-- ===========================
-- TABLE: saw_criterias
-- ===========================
CREATE TABLE `saw_criterias` (
  `id_criteria` TINYINT(3) UNSIGNED NOT NULL AUTO_INCREMENT,
  `criteria` VARCHAR(100) NOT NULL,
  `weight` FLOAT NOT NULL,
  `attribute` ENUM('benefit','cost'),
  PRIMARY KEY (`id_criteria`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO saw_criterias (id_criteria, criteria, weight, attribute) VALUES
(1, 'Waktu Pengiriman Barang ke Telkomsel', 25, 'benefit'),
(2, 'Kualitas Barang', 20, 'benefit'),
(3, 'Solusi Permasalahan', 15, 'benefit'),
(4, 'Penyelesaian Dokumen Pendukung & Berita Acara', 20, 'benefit'),
(5, 'Waktu Penyelesaian Masalah', 20, 'benefit');

-- ===========================
-- TABLE: saw_evaluations
-- ===========================
CREATE TABLE `saw_evaluations` (
  `id_eval` INT AUTO_INCREMENT PRIMARY KEY,

  `id_alternative` SMALLINT UNSIGNED NOT NULL,
  `id_criteria` TINYINT UNSIGNED NOT NULL,

  `value` ENUM('1','2','3','4','5') NOT NULL,
  `period` CHAR(7) NOT NULL,

  `created_by` INT NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,

  `updated_by` INT DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,

  KEY `idx_alt` (`id_alternative`),
  KEY `idx_criteria` (`id_criteria`),

  CONSTRAINT `fk_eval_alt`
    FOREIGN KEY (`id_alternative`)
    REFERENCES `saw_alternatives` (`id_alternative`)
    ON DELETE CASCADE,

  CONSTRAINT `fk_eval_criteria`
    FOREIGN KEY (`id_criteria`)
    REFERENCES `saw_criterias` (`id_criteria`)
    ON DELETE CASCADE,

  CONSTRAINT `fk_eval_created_by`
    FOREIGN KEY (`created_by`)
    REFERENCES `saw_users` (`id_user`),

  CONSTRAINT `fk_eval_updated_by`
    FOREIGN KEY (`updated_by`)
    REFERENCES `saw_users` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `saw_notifications` (
  `id_notification` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(100) NOT NULL,
  `message` TEXT NOT NULL,
  is_evaluation TINYINT(1) NOT NULL DEFAULT 0,

  -- user yang membuat notifikasi
  `created_by` INT NOT NULL,

  -- jika NULL → notifikasi global
  `target_role` ENUM('admin','manager','mitra','quality_control') DEFAULT NULL,

  -- jika NULL → untuk semua user
  `target_user_id` INT DEFAULT NULL,

  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,

  KEY `idx_target_role` (`target_role`),
  KEY `idx_target_user` (`target_user_id`),
  KEY `idx_created_by` (`created_by`),

  CONSTRAINT `fk_notification_creator`
    FOREIGN KEY (`created_by`) REFERENCES `saw_users` (`id_user`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ===========================
-- DUMMY DATA EVALUATIONS (TETAP)
-- ===========================
INSERT INTO saw_evaluations
(id_alternative, id_criteria, value, period, created_by)
SELECT
  a.id_alternative,
  c.id_criteria,
  CAST(ROUND(1 + (RAND() * 4), 0) AS CHAR),
  p.period,
  1
FROM saw_alternatives a
CROSS JOIN saw_criterias c
CROSS JOIN (
  SELECT '2023-01' AS period UNION ALL
  SELECT '2023-02' UNION ALL
  SELECT '2023-03' UNION ALL
  SELECT '2023-04' UNION ALL
  SELECT '2023-05' UNION ALL
  SELECT '2023-06' UNION ALL
  SELECT '2023-07' UNION ALL
  SELECT '2023-08' UNION ALL
  SELECT '2023-09' UNION ALL
  SELECT '2023-10' UNION ALL
  SELECT '2023-11' UNION ALL
  SELECT '2023-12' UNION ALL
  SELECT '2024-01' UNION ALL
  SELECT '2024-02' UNION ALL
  SELECT '2024-03' UNION ALL
  SELECT '2024-04' UNION ALL
  SELECT '2024-05' UNION ALL
  SELECT '2024-06' UNION ALL
  SELECT '2024-07' UNION ALL
  SELECT '2024-08' UNION ALL
  SELECT '2024-09' UNION ALL
  SELECT '2024-10' UNION ALL
  SELECT '2024-11' UNION ALL
  SELECT '2024-12' UNION ALL
  SELECT '2025-01' UNION ALL
  SELECT '2025-02' UNION ALL
  SELECT '2025-03' UNION ALL
  SELECT '2025-04' UNION ALL
  SELECT '2025-05' UNION ALL
  SELECT '2025-06' UNION ALL
  SELECT '2025-07' UNION ALL
  SELECT '2025-08' UNION ALL
  SELECT '2025-09' UNION ALL
  SELECT '2025-10' UNION ALL
  SELECT '2025-11' UNION ALL
  SELECT '2025-12' UNION ALL
  SELECT '2026-01'
) p;
