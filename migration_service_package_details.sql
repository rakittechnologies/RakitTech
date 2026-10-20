-- Jalankan hanya jika instalasi lama tidak mengizinkan migrasi otomatis dari aplikasi.
ALTER TABLE service_packages
  ADD COLUMN full_description LONGTEXT NULL,
  ADD COLUMN suitable_for LONGTEXT NULL,
  ADD COLUMN work_duration VARCHAR(255) NULL,
  ADD COLUMN revision_terms LONGTEXT NULL,
  ADD COLUMN included_items LONGTEXT NULL,
  ADD COLUMN excluded_items LONGTEXT NULL;
