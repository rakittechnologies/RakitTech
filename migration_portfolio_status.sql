-- Tambahkan status portofolio untuk membedakan demo, proyek internal, dan proyek yang sudah digunakan klien.
-- Jalankan satu kali pada database RuangWeb yang digunakan.
ALTER TABLE projects ADD COLUMN project_status VARCHAR(80) NOT NULL DEFAULT 'Demo / contoh konsep' AFTER sales_method;
