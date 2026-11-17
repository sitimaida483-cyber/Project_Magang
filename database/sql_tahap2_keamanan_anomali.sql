-- ==============================================================================
-- SKRIP SQL PENAMBAHAN TELEMETRI & DETEKSI ANOMALI PRESTIK (TAHAP 2)
-- ==============================================================================
-- Proyek    : PresTIK (Presensi Digital Bidtik Polda Kalsel)
-- Tanggal   : 16 November 2026
-- Sifat     : 100% Penambahan kolom baru (Additive & Nullable)
-- Eksekusi  : Dapat langsung di-import/dijalankan pada menu SQL di phpMyAdmin hosting
-- ==============================================================================

ALTER TABLE `presensi`
  ADD COLUMN `ip_address` VARCHAR(45) NULL DEFAULT NULL AFTER `status_kehadiran`,
  ADD COLUMN `user_agent` TEXT NULL DEFAULT NULL AFTER `ip_address`,
  ADD COLUMN `akurasi_gps` DECIMAL(8,2) NULL DEFAULT NULL AFTER `user_agent`,
  ADD COLUMN `foto_hash` VARCHAR(64) NULL DEFAULT NULL AFTER `akurasi_gps`,
  ADD COLUMN `flag_anomali` TINYINT(1) NOT NULL DEFAULT 0 AFTER `foto_hash`,
  ADD COLUMN `catatan_anomali` TEXT NULL DEFAULT NULL AFTER `flag_anomali`,
  ADD INDEX `presensi_foto_hash_idx` (`foto_hash`),
  ADD INDEX `presensi_flag_anomali_idx` (`flag_anomali`);

