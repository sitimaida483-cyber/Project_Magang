-- ==============================================================================
-- SQL MANUAL UNTUK PHPMYADMIN (HOSTING / CPANEL)
-- REVISI SISTEM PRESENSI BIDTIK POLDA KALSEL
-- ==============================================================================
-- File ini dapat di-copy paste langsung ke tab "SQL" di phpMyAdmin pada database hosting
-- jika tidak memiliki akses Terminal / SSH.
-- Perubahan bersifat penambahan (additive), data lama tetap aman.
-- ==============================================================================

-- 1. Penambahan kolom email, password, dan remember_token pada tabel anggota
ALTER TABLE `anggota`
    ADD COLUMN `email` VARCHAR(255) NULL UNIQUE AFTER `nama`,
    ADD COLUMN `password` VARCHAR(255) NULL AFTER `email`,
    ADD COLUMN `remember_token` VARCHAR(100) NULL AFTER `status`;

-- 2. Pembuatan tabel pengajuan_surat (untuk izin, sakit, cuti, surat dinas, dll dengan PDF)
CREATE TABLE IF NOT EXISTS `pengajuan_surat` (
    `id_pengajuan` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_anggota` BIGINT UNSIGNED NOT NULL,
    `jenis_keterangan` ENUM(
        'izin',
        'sakit',
        'surat_dinas',
        'cuti',
        'piket',
        'lepas_dinas',
        'wfh',
        'pendidikan'
    ) NOT NULL,
    `tanggal_mulai` DATE NOT NULL,
    `tanggal_selesai` DATE NOT NULL,
    `keterangan` TEXT NOT NULL,
    `file_surat` VARCHAR(255) NOT NULL,
    `status_pengajuan` ENUM('menunggu', 'disetujui', 'ditolak') NOT NULL DEFAULT 'menunggu',
    `id_admin` BIGINT UNSIGNED NULL DEFAULT NULL,
    `catatan_admin` TEXT NULL DEFAULT NULL,
    `tanggal_diproses` DATETIME NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_pengajuan`),
    INDEX `idx_anggota_status` (`id_anggota`, `status_pengajuan`),
    INDEX `idx_periode_surat` (`tanggal_mulai`, `tanggal_selesai`),
    INDEX `idx_status_pengajuan` (`status_pengajuan`),
    CONSTRAINT `fk_pengajuan_anggota`
        FOREIGN KEY (`id_anggota`) REFERENCES `anggota` (`id_anggota`)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_pengajuan_admin`
        FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

